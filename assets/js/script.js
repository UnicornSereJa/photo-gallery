document.addEventListener("DOMContentLoaded", () => {
  const images = document.querySelectorAll(".gallery .card");

  images.forEach(img => {
    img.classList.add("hidden");
  });

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  images.forEach(img => observer.observe(img));
});

// Драг энд дроп (типо перенеси чтобы загурзить)
const dropZone=document.getElementById('drop-zone');
const fileInput=document.getElementById('fileInput');
dropZone.addEventListener('dragover',e=>{
    e.preventDefault();
    dropZone.classList.add('dragover');
});
dropZone.addEventListener('dragleave',()=>dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop',e=>{
    e.preventDefault();
    dropZone.classList.remove('dragover');
    fileInput.files=e.dataTransfer.files;
});
