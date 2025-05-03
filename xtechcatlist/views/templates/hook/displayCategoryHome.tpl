{if !empty($xtech_categories)}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <div class="xtech-list">
        <p class="small"><span class="rect-style"></span> Categories</p>
        
        <div class="d-flex flex-row align-items-center justify-content-between">
            <h2 class="browser_title">Browse By Category :</h2>
            <div class="d-flex gap-2">
                <span class="btn btn-light chevron chevron-left">
                    <i class="bi bi-chevron-left"></i>
                </span>
                <span class="btn btn-light chevron chevron-right">
                    <i class="bi bi-chevron-right"></i>
                </span>
            </div>
        </div>
        
        <div class="category-list-wrapper">
            <div class="category-list list-d-flex">
                {foreach from=$xtech_categories item=category}
                    <div class="category-item-list">
                        <a href="{$category.url}" class="row">
                            <img src="{$category.image}" alt="{$category.name}" class="category-image-list">
                            <p class="category-title-list col-md-10">{$category.name}</p>
                        </a>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>

    <style>
        .category-list-wrapper {
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        

        .category-item-list {
            min-width: 200px; /* Adjust size as needed */
            margin-right: 15px;
            text-align: center;
        }

        .chevron {
            cursor: pointer;
            user-select: none;
        }
    </style>
{literal}
    

    <script>
     document.addEventListener("DOMContentLoaded", function () {
    const categoryList = document.querySelector(".list-d-flex");
    const chevronLeft = document.querySelector(".chevron-left");
    const chevronRight = document.querySelector(".chevron-right");

    let currentTranslate = 0;
    const scrollAmount = 220; // Distance de déplacement
    const maxScroll = categoryList.scrollWidth - categoryList.parentElement.clientWidth; 

    function updateScroll() {
        if (currentTranslate > 0) {
            currentTranslate = 0; // Bloque à gauche
        } else if (Math.abs(currentTranslate) > maxScroll) {
            currentTranslate = -maxScroll; // Bloque à droite
        }
        categoryList.style.transform = `translateX(${currentTranslate}px)`;
    }

    chevronLeft.addEventListener("click", () => { 
        currentTranslate += scrollAmount;
        updateScroll();
    });

    chevronRight.addEventListener("click", () => {
        currentTranslate -= scrollAmount;
        updateScroll();
    });
});

    </script>
    {/literal}
{/if}
