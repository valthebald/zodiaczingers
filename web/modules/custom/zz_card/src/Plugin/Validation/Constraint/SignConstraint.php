<?php

namespace Drupal\zz_card\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Verifies zodiac sign value.
 */
#[Constraint(
  id: 'ZodiacSign',
  label: new TranslatableMarkup('Valid Zodiac sign', [], ['context' => 'Validation'])
)]
class SignConstraint extends SymfonyConstraint {

  public $message = 'Invalid state transition from %from to %to';
  public $invalidStateMessage = 'State %state does not exist on %workflow workflow';
  public $invalidTransitionAccess = 'You do not have access to transition from %original_state to %new_state';

}
