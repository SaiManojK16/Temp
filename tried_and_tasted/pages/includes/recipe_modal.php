<?php // pages/includes/recipe_modal.php ?>
<style>
/* Modal backdrop */
.modal {
  display: none;
  position: fixed; top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
/* Modal box */
.modal-content {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}
.modal-content .close {
  position: absolute;
  top: 10px; right: 15px;
  font-size: 24px;
  cursor: pointer;
}
.modal-content h2 {
  margin-top: 0;
  font-size: 1.5rem;
}
.modal-content h3 {
  margin: 1rem 0 .5rem;
}
.modal-content ul {
  list-style: disc;
  margin-left: 1.2rem;
}
.modal-content button {
  margin-right: 1rem;
  padding: .5rem 1rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
#cook-results {
  margin-top: 1rem;
}
</style>

<div id="recipe-modal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="modal-title"></h2>
    <p>
      <strong>Diet:</strong> <span id="modal-diet"></span> |
      <strong>Calories:</strong> <span id="modal-cal"></span>
    </p>
    <h3>Ingredients</h3>
    <ul id="modal-ingredients"></ul>
    <h3>Instructions</h3>
    <p id="modal-instr"></p>
    <button id="cook-btn">Cook</button>
    <button id="add-to-mealplan-btn">Add to Meal Plan</button>
    <div id="cook-results" style="display:none">
      <h3>Have</h3>
      <ul id="have-list"></ul>
      <h3>Need</h3>
      <ul id="need-list"></ul>
    </div>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('recipe-modal');
  const closeBtn = modal.querySelector('.close');
  let currentId = null;

  // Close helpers
  closeBtn.onclick = () => modal.style.display = 'none';
  window.onclick = e => { if(e.target===modal) modal.style.display='none'; };

  // Delegate click on any .recipe-card[data-id]
  document.addEventListener('click', e => {
    const card = e.target.closest('.recipe-card[data-id]');
    if (!card) return;
    currentId = card.getAttribute('data-id');
    openModal(currentId);
  });

  function openModal(id) {
    fetch(`../api/recipes/details.php?recipe_id=${id}`, { credentials: 'include' })
      .then(r => r.json())
      .then(data => {
        if (data.error) { alert(data.error); return; }
        modal.querySelector('#modal-title').innerText       = data.title;
        modal.querySelector('#modal-diet').innerText        = data.diet_preference || '—';
        modal.querySelector('#modal-cal').innerText         = data.total_calories || '—';
        modal.querySelector('#modal-instr').innerText       = data.instructions;
        modal.querySelector('#modal-ingredients').innerHTML =
          data.ingredients.map(i =>
            `<li data-iid="${i.ingredient_id}" data-qty="${i.quantity}">
              ${i.name} — ${i.quantity}
            </li>`
          ).join('');
        modal.style.display = 'flex';
        modal.querySelector('#cook-results').style.display = 'none';
      });
  }

  // Cook button
  document.getElementById('cook-btn').onclick = () => {
    fetch(`../api/pantry/list.php`, { credentials:'include' })
      .then(r => r.json())
      .then(pantry => {
        const haveUl = modal.querySelector('#have-list');
        const needUl = modal.querySelector('#need-list');
        haveUl.innerHTML = needUl.innerHTML = '';
        modal.querySelectorAll('#modal-ingredients li').forEach(li => {
          const iid = +li.dataset.iid,
                req = +li.dataset.qty,
                pan = pantry.find(p=>+p.ingredient_id===iid);
          if (pan && +pan.quantity >= req) {
            haveUl.innerHTML += `<li>${li.innerText}</li>`;
          } else {
            needUl.innerHTML += `<li>${li.innerText}` +
              (pan ? ` (have ${pan.quantity})` : '') +
              ` <button onclick="addToCart(${iid},${req})">Add to Cart</button></li>`;
          }
        });
        modal.querySelector('#cook-results').style.display = 'block';
      });
  };

  // Meal plan
  document.getElementById('add-to-mealplan-btn').onclick = () => {
    window.location.href = `meal_planner.php?recipe_id=${currentId}`;
  };

  // global for the inline button
  window.addToCart = (iid,qty) => {
    window.location.href = `cart.php?add=${iid}&qty=${qty}`;
  };
})();
</script>
