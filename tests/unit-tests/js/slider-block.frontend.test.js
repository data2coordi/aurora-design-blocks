/**
 * @jest-environment jsdom
 */

import '@testing-library/jest-dom';
import { fireEvent } from '@testing-library/dom';

// フロントエンドスクリプト本体（DOMContentLoadedの中身を関数化）
function initializeSlider() {
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

        // スライドボタン作成
        const prevButton = document.createElement('button');
        prevButton.className = 'slide-button prev';
        prevButton.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';

        const nextButton = document.createElement('button');
        nextButton.className = 'slide-button next';
        nextButton.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

        // 親ブロックにボタン追加
        const sliderBlock = container.closest('.wp-block-aurora-design-blocks-slider-block');
        sliderBlock.appendChild(prevButton);
        sliderBlock.appendChild(nextButton);

        let currentIndex = 0;
        let autoSlide;

        function setActiveBlockSlider(index) {
            blockSliders.forEach((slide, i) => {
                slide.style.opacity = i === index ? '1' : '0';
                slide.style.transition = 'opacity 0.5s ease-in-out';
            });
            currentIndex = index;
        }

        function nextBlockSlider() {
            setActiveBlockSlider((currentIndex + 1) % blockSliders.length);
        }

        function prevBlockSlider() {
            setActiveBlockSlider((currentIndex - 1 + blockSliders.length) % blockSliders.length);
        }

        function resetAutoSlide() {
            clearInterval(autoSlide);
            autoSlide = setInterval(nextBlockSlider, 5000);
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
}

describe('aurora-design-blocks slider frontend script', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div class="wp-block-aurora-design-blocks-slider-block blockSliders">
                <div class="blockSliders-content">
                    <div class="slide" style="opacity:0">Slide 1</div>
                    <div class="slide" style="opacity:0">Slide 2</div>
                    <div class="slide" style="opacity:0">Slide 3</div>
                </div>
            </div>
        `;

        delete window.wp;
        jest.useFakeTimers();
        jest.spyOn(global, 'setInterval');
    });

    afterEach(() => {
        jest.runOnlyPendingTimers();
        jest.useRealTimers();
        jest.restoreAllMocks();
    });

    test('initializes slider, creates buttons, shows first slide', () => {
        initializeSlider();

        const prevButton = document.querySelector('.slide-button.prev');
        const nextButton = document.querySelector('.slide-button.next');
        expect(prevButton).toBeInTheDocument();
        expect(nextButton).toBeInTheDocument();

        const slides = document.querySelectorAll('.blockSliders-content > div');
        expect(slides[0].style.opacity).toBe('1');
        expect(slides[1].style.opacity).toBe('0');
        expect(slides[2].style.opacity).toBe('0');

        expect(setInterval).toHaveBeenCalledWith(expect.any(Function), 5000);
    });

    test('clicking next and prev buttons changes slides correctly', () => {
        initializeSlider();

        const slides = document.querySelectorAll('.blockSliders-content > div');
        const prevButton = document.querySelector('.slide-button.prev');
        const nextButton = document.querySelector('.slide-button.next');

        expect(slides[0].style.opacity).toBe('1');
        expect(slides[1].style.opacity).toBe('0');

        fireEvent.click(nextButton);
        expect(slides[0].style.opacity).toBe('0');
        expect(slides[1].style.opacity).toBe('1');

        fireEvent.click(prevButton);
        expect(slides[0].style.opacity).toBe('1');
        expect(slides[1].style.opacity).toBe('0');
    });

    test('auto slide changes slide every 5 seconds', () => {
        initializeSlider();

        const slides = document.querySelectorAll('.blockSliders-content > div');

        expect(slides[0].style.opacity).toBe('1');

        jest.advanceTimersByTime(5000);
        expect(slides[1].style.opacity).toBe('1');

        jest.advanceTimersByTime(5000);
        expect(slides[2].style.opacity).toBe('1');

        jest.advanceTimersByTime(5000);
        expect(slides[0].style.opacity).toBe('1');
    });

    test('does nothing if window.wp.blocks exists', () => {
        window.wp = { blocks: {} };

        const spyConsole = jest.spyOn(console, 'log').mockImplementation(() => { });

        initializeSlider();

        expect(spyConsole).toHaveBeenCalledWith('Block editor detected, skipping frontend script.');

        expect(document.querySelector('.slide-button.prev')).not.toBeInTheDocument();
        expect(document.querySelector('.slide-button.next')).not.toBeInTheDocument();

        spyConsole.mockRestore();
    });
});
