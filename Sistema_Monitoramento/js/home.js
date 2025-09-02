function toggleAccordion() {
  const content = document.getElementById('accordion-content');
  const icon = document.getElementById('accordion-icon');
  content.classList.toggle('hidden');
  icon.textContent = content.classList.contains('hidden') ? '⌄' : '⌃';
}