{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 * 
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}

 <div class="row">
 <div class="col-md-2" id="leftCategory">
 {hook h='displayCategoryHome'}
 </div>

 {if $homeslider.slides}
   <div id="carousel" data-ride="carousel" class="carousel slide col-md-10" data-interval="{$homeslider.speed}" data-wrap="{(string)$homeslider.wrap}" data-pause="{$homeslider.pause}" data-touch="true">
     

     <!-- Carousel Items -->
     <ul class="carousel-inner" role="listbox" aria-label="{l s='Carousel container' d='Shop.Theme.Global'}">
       {foreach from=$homeslider.slides item=slide key=idxSlide name='homeslider'}
         <li class="carousel-item {if $idxSlide == 0}active{/if}" role="option">
           <a href="{$slide.url}">
             <figure>
               <img src="{$slide.image_url}" alt="{$slide.legend|escape}" loading="lazy"  height="340">
               {if $slide.title || $slide.description}
                 <figcaption class="caption">
                   <h2 class="display-1 text-uppercase">{$slide.title}</h2>
                   <button class="shop_now"> SHOW NOW</button>
                 </figcaption>
               {/if}
             </figure>
           </a>
         </li>
       {/foreach}
     </ul>

     <!-- Carousel Controls -->
     <a class="carousel-control-prev" href="#carousel" role="button" data-slide="prev">
       <span class="carousel-control-prev-icon" aria-hidden="true"></span>
       <span class="sr-only">Previous</span>
     </a>
     <a class="carousel-control-next" href="#carousel" role="button" data-slide="next">
       <span class="carousel-control-next-icon" aria-hidden="true"></span>
       <span class="sr-only">Next</span>
     </a>

     <!-- Dynamic Dots -->
     <div class="dot">
       <p class="dot-line">
         {foreach from=$homeslider.slides item=slide key=idxSlide}
           <span class="dot-element {if $idxSlide == 0}active{/if}" data-slide-to="{$idxSlide}"></span>
         {/foreach}
       </p>
     </div>
     
   </div>
 {/if}
</div>

<!-- JavaScript to Handle Dot Click & Auto Update -->
<script>
document.addEventListener("DOMContentLoaded", function () {
 let dots = document.querySelectorAll(".dot-element");
 let carousel = document.querySelector("#carousel");

 // Handle click on dots
 dots.forEach((dot, index) => {
   dot.addEventListener("click", function () {
     $('.carousel').carousel(index); // Change slide
     updateActiveDot(index);
   });
 });

 // Update active dot when the slide changes automatically
 $('#carousel').on('slid.bs.carousel', function (event) {
   updateActiveDot(event.to);
 });

 // Function to update active dot
 function updateActiveDot(index) {
   document.querySelector(".dot-element.active")?.classList.remove("active");
   dots[index].classList.add("active");
 }
});
</script>

<!-- CSS for Dot Styling -->
<style>
.dot {
 text-align: center;
 margin-top: 15px;
}
.dot-line {
 display: flex;
 justify-content: center;
 gap: 8px;
}
.dot-element {
 width: 10px;
 height: 10px;
 background: gray;
 border-radius: 50%;
 display: inline-block;
 cursor: pointer;
 transition: background 0.3s;
}
.dot-element.active {
 background: red; /* 🔥 Le dot actif devient rouge */
}
</style>
