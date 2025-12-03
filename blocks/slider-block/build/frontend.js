/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!*************************!*\
  !*** ./src/frontend.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initializeSlider: () => (/* binding */ initializeSlider)
/* harmony export */ });
function initializeSlider() {
  // ブロックエディター内では処理しない

  const blockSliderContainers = document.querySelectorAll(".wp-block-aurora-design-blocks-slider-block.blockSliders > .blockSliders-content");
  blockSliderContainers.forEach(container => {
    const blockSliders = Array.from(container.children);
    if (blockSliders.length === 0) return;

    //console.log('Found block sliders:', blockSliders);

    // スライドボタンを作成
    const prevButton = document.createElement("button");
    prevButton.className = "slide-button prev";
    prevButton.innerHTML = '<span class="icon-leftarrow"></span>';
    //prevButton.innerHTML = '<i class="icon-leftarrow"></i>';

    const nextButton = document.createElement("button");
    nextButton.className = "slide-button next";
    nextButton.innerHTML = '<span class="icon-rightarrow"></span>';

    // `.wp-block-aurora-design-blocks-slider-block` にボタンを追加
    const sliderBlock = container.closest(".wp-block-aurora-design-blocks-slider-block");
    sliderBlock.appendChild(prevButton);
    sliderBlock.appendChild(nextButton);
    let currentIndex = 0;
    let autoSlide; // スコープを適切に設定

    function setActiveBlockSlider(index) {
      blockSliders.forEach((t, i) => {
        t.style.opacity = i === index ? "1" : "0";
        t.style.transition = "opacity 0.5s ease-in-out";
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
    nextButton.addEventListener("click", () => {
      nextBlockSlider();
      resetAutoSlide();
    });
    prevButton.addEventListener("click", () => {
      prevBlockSlider();
      resetAutoSlide();
    });
    setActiveBlockSlider(0);
    resetAutoSlide();
  });
}
document.addEventListener("DOMContentLoaded", () => {
  // wp.blocks が存在しない場合にだけ実行（WordPress仕様にあわせる）
  initializeSlider();
});
/******/ })()
;
//# sourceMappingURL=frontend.js.map