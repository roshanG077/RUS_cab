/* ============================================================
   RUS CAB — rate.js
   Star rating selector and quick-tag toggle logic
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  const starBtns  = document.querySelectorAll('.star-btn');
  const starInput = document.getElementById('star_input');
  const starLabel = document.getElementById('star_label');

  const labels = { 1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Excellent' };

  starBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const val = Number(btn.dataset.value);
      starInput.value = val;
      starLabel.textContent = labels[val];

      starBtns.forEach(b => {
        b.classList.toggle('active', Number(b.dataset.value) <= val);
      });
    });

    /* Hover preview */
    btn.addEventListener('mouseenter', () => {
      const val = Number(btn.dataset.value);
      starBtns.forEach(b => {
        b.classList.toggle('hovered', Number(b.dataset.value) <= val);
      });
    });

    btn.addEventListener('mouseleave', () => {
      starBtns.forEach(b => b.classList.remove('hovered'));
    });
  });

  /* Quick tags toggle */
  document.querySelectorAll('.rate-tag').forEach(tag => {
    tag.addEventListener('click', () => tag.classList.toggle('selected'));
  });

});