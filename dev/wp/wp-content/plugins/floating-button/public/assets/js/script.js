/**
 * FloatingButton
 * A JavaScript class for creating customizable floating button.
 *
 * @version 1.0.0
 * @author Dmytro Lobov
 */

'use strict';

class FloatingButton {

  constructor(options) {

    const defaultOptions = {
      showAfterTimer: false,
      hideAfterTimer: false,
      showAfterPosition: false,
      hideAfterPosition: false,
      uncheckedBtn: false,
      uncheckedSubBtn: false,
      hideBtns: false,
      touch: false,
      fontSize: parseFloat(window.getComputedStyle(document.body).getPropertyValue('font-size')),
      fontSizeStep: 10,
    };

    this.settings = {...defaultOptions, ...options};
    this.element = document.getElementById(this.settings.element);
  }

  displayMenu() {
    this.showMenu();
  }

  showMenu() {
    this.element.classList.remove('is-hidden');

  }

  run() {
    this.displayMenu();
  }

  static initialize(options) {
    const flbtn = new FloatingButton(options);
    flbtn.run();
    return flbtn;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  for (let key in window) {
    if (key.indexOf('FloatingButton_') >= 0) {
      const val = window[key];
      new FloatingButton(val);
      FloatingButton.initialize(val);
    }
  }
});