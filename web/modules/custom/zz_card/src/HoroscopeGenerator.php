<?php

namespace Drupal\zz_card;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\OperationType\Chat\ChatInput;
use Drupal\ai\OperationType\Chat\ChatMessage;
use Drupal\ai_translate\TextExtractorInterface;
use Drupal\filter\Entity\FilterFormat;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Defines a Horoscope generator
 */
class HoroscopeGenerator {

  /**
   * The module handler for the hooks.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * @param \Drupal\ai\AiProviderPluginManager $aiProviderManager
   *   AI provider plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   *   The Twig template engine.
   */
  public function __construct(
    protected AiProviderPluginManager $aiProviderManager,
    protected ConfigFactoryInterface $configFactory,
    protected TwigEnvironment $twig,
  ) {}

  /**
   * Get the text translated by AI API call.
   *
   * @param string $input_text
   *   Input prompt for the LLm.
   * @param \Drupal\Core\Language\LanguageInterface $langFrom
   *   Source language.
   * @param \Drupal\Core\Language\LanguageInterface $langTo
   *   Destination language.
   *
   * @return string
   *   Translated content.
   */
  public function generateHoroscope(Sign $sign) {
    $config = $this->configFactory->get('ai.settings')->get('default_providers');
    if (empty($config) || empty($config['chat'])) {
      throw new NotFoundHttpException();
    }
    $provider = $this->aiProviderManager->createInstance($config['chat']['provider_id']);
    $prompt =<<<PROMPT
Write a horoscope for today for {{ sign }}. Horoscope should consist of 2 paragraphs
of text of average length.
Return the result as HTML fragment (i.e., wrap paragraphs in with <p> tag)".
PROMPT;
    $promptText = $this->twig->renderInline($prompt, [
      'sign' => $sign->name,
    ]);
    try {
      $messages = new ChatInput([
        new chatMessage('system', 'You are a horoscope maker.'),
        new chatMessage('user', $promptText),
      ]);

      /** @var /Drupal\ai\OperationType\Chat\ChatOutput $message */
      $message = $provider->chat($messages, $config['chat']['model_id'])->getNormalized();
    }
    catch (GuzzleException $exception) {
      // Error handling for the API call.
      return $exception->getMessage();
    }
    $cleaned = trim(trim($message->getText(), '```'), ' ');
    return trim($cleaned, '"');
  }

  /**
   * Get the preferred provider if configured, else take the default one.
   *
   * @param string $preferred_model
   *   The preferred model as a string.
   * @param string $operationType
   *   The operation type (like chat).
   *
   * @return array|null
   *   An array with the model and provider.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   An exception.
   */
  public function getSetProvider($preferred_model, $operationType) {
    // Check if there is a preferred model.
    $provider = NULL;
    $model = NULL;
    if ($preferred_model) {
      $provider = $this->aiProviderManager->loadProviderFromSimpleOption($preferred_model);
      $model = $this->aiProviderManager->getModelNameFromSimpleOption($preferred_model);
    }
    else {
      // Get the default provider.
      $default_provider = $this->aiProviderManager->getDefaultProviderForOperationType($operationType);
      if (empty($default_provider['provider_id'])) {
        // If we got nothing return NULL.
        return NULL;
      }
      $provider = $this->aiProviderManager->createInstance($default_provider['provider_id']);
      $model = $default_provider['model_id'];
    }
    return [
      'provider_id' => $provider,
      'model_id' => $model,
    ];
  }

  /**
   * Finished operation.
   */
  public static function finish($success, $results, $operations, $duration) {
    $messenger = \Drupal::messenger();
    if ($success) {
      $messenger->addMessage(t('All terms have been processed.'));
    }
  }

  /**
   * Batch callback - translate a single text.
   *
   * @param array $singleText
   *   Chunk of text metadata to translate.
   * @param \Drupal\Core\Language\LanguageInterface $langFrom
   *   The source language.
   * @param \Drupal\Core\Language\LanguageInterface $langTo
   *   The target language.
   * @param array $context
   *   The batch context.
   */
  public function translateSingleText(
    array $singleText,
    LanguageInterface $langFrom,
    LanguageInterface $langTo,
    array &$context,
  ) {
    // Translate the content.
    $translated_text = $this->translateContent(
      $singleText['value'], $langFrom, $langTo
    );

    // Checks if the field allows HTML and decodes the HTML entities.
    if (isset($singleText['format'])) {
      $format = $singleText['format'];
      if (FilterFormat::load($format)) {
        $translated_text = html_entity_decode($translated_text);
      }
    }

    $singleText['translated'] = $translated_text;
    $context['results']['processedTranslations'][] = $singleText;
  }

  /**
   * Batch callback - insert processed texts back into the entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to translate.
   * @param string $lang_to
   *   Language code of translation.
   * @param array $context
   *   Text metadata containing both source values and translation.
   */
  public function insertTranslation(
    ContentEntityInterface $entity,
    string $lang_to,
    array &$context,
  ) {
    $translation = $entity->addTranslation($lang_to);
    $this->textExtractor->insertTextMetadata($translation,
      $context['results']['processedTranslations']);
    try {
      $translation->save();
      $this->messenger()->addStatus($this->t('Content translated successfully.'));
    }
    catch (\Throwable $exception) {
      $this->getLogger('ai_translate')->warning($exception->getMessage());
      $this->messenger()->addError($this->t('There was some issue with content translation.'));
    }
  }

}
