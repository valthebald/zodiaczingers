document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll('[data-card]').forEach(async function (card) {
    const url = Drupal.url('card/' + card.getAttribute('data-card')
    + '/' + card.getAttribute('data-date') + '/load');
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Content-Type": "application/json"
      }
    });

    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    const data = await response.json();
    card.innerHTML = data;
  });
});
