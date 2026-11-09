document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.dafap-tab-nav li');
  const contents = document.querySelectorAll('.dafap-tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      contents.forEach(c => c.classList.remove('active'));

      tab.classList.add('active');
      document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
    });
  });
});
