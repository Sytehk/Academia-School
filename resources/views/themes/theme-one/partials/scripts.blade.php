 {{-- AOS + Alpine (offline-safe) --}}
 @include('partials.local-assets.scripts', ['aos' => true, 'alpine' => true])

 <script>
     AOS.init({
         duration: 1000,
         once: true,
     });

     document.querySelectorAll('.card-3d').forEach(el => {
         el.addEventListener('mousemove', (e) => {
             const rect = el.getBoundingClientRect();
             const x = e.clientX - rect.left;
             const y = e.clientY - rect.top;

             const rotateX = ((y / rect.height) - 0.5) * -10;
             const rotateY = ((x / rect.width) - 0.5) * 10;

             el.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
         });

         el.addEventListener('mouseleave', () => {
             el.style.transform = '';
         });
     });

     function toggleFAQ(id) {

         const answer = document.getElementById('faq' + id);
         const icon = document.getElementById('icon' + id);

         const isOpen = answer.style.maxHeight && answer.style.maxHeight !== "0px";

         // close all first
         document.querySelectorAll('.faq-answer').forEach(el => {
             el.style.maxHeight = null;
         });

         document.querySelectorAll('[id^="icon"]').forEach(i => {
             i.innerHTML = "+";
         });

         if (!isOpen) {
             answer.style.maxHeight = answer.scrollHeight + "px";
             icon.innerHTML = "×";
         } else {
             answer.style.maxHeight = null;
             icon.innerHTML = "+";
         }
     }
 </script>
