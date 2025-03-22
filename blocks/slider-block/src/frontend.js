document.addEventListener('DOMContentLoaded', function () {
    // ブロックエディター内では処理しない
    if (window.wp && wp.blocks) {
        console.log('Block editor detected, skipping frontend script.');
        return;
    }

    const blockSliderContainers = document.querySelectorAll(
        '.wp-block-aurora-design-blocks-slider-block.blockSliders > .blockSliders-content'
    );

    blockSliderContainers.forEach(container => {
        const blockSliders = Array.from(container.children);
        if (blockSliders.length === 0) return;

        console.log('Found block sliders:', blockSliders);

        // スライドボタンを作成
        const prevButton = document.createElement('button');
        prevButton.className = 'slide-button prev';
        prevButton.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';

        const nextButton = document.createElement('button');
        nextButton.className = 'slide-button next';
        nextButton.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

        // `.wp-block-aurora-design-blocks-slider-block` にボタンを追加
        const sliderBlock = container.closest('.wp-block-aurora-design-blocks-slider-block');
        sliderBlock.appendChild(prevButton);
        sliderBlock.appendChild(nextButton);

        let currentIndex = 0;
        let autoSlide; // スコープを適切に設定

        function setActiveBlockSlider(index) {
            blockSliders.forEach((t, i) => {
                t.style.opacity = i === index ? '1' : '0';
                t.style.transition = 'opacity 0.5s ease-in-out';
            });
            currentIndex = index;
        }

        function nextBlockSlider() {
            const nextIndex = (currentIndex + 1) % blockSliders.length;
            setActiveBlockSlider(nextIndex);
        }

        function prevBlockSlider() {
            const prevIndex = (currentIndex - 1 + blockSliders.length) % blockSliders.length;
            setActiveBlockSlider(prevIndex);
        }

        function resetAutoSlide() {
            clearInterval(autoSlide);
            autoSlide = setInterval(nextBlockSlider, 5000); // 5秒ごとにスライド
        }

        nextButton.addEventListener('click', () => {
            nextBlockSlider();
            resetAutoSlide();
        });

        prevButton.addEventListener('click', () => {
            prevBlockSlider();
            resetAutoSlide();
        });

        setActiveBlockSlider(0);
        resetAutoSlide();
    });
});
