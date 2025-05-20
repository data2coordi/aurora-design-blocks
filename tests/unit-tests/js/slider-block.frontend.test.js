/**
 * @jest-environment jsdom
 */

import '@testing-library/jest-dom';
import { fireEvent } from '@testing-library/dom';




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

        jest.useFakeTimers();
        jest.spyOn(global, 'setInterval');
        ({ initializeSlider } = require('../../../blocks/slider-block/src/frontend.js'));

    });

    afterEach(() => {
        //jest.runOnlyPendingTimers();
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

});
