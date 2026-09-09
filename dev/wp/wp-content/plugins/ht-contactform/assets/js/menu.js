'use strict';

document.addEventListener('DOMContentLoaded', function() {
    // Check current hash on page load
    const submenuItems = document.querySelectorAll('#toplevel_page_htcontact-form .wp-submenu a');
    submenuItems.forEach(function(item) {
        if(item.href === window.location.href) {
            item.parentElement.classList.add('current');
            // Remove 'current' class from siblings
            const siblings = Array.from(item.parentElement.parentElement.children);
            siblings.forEach(function(sibling) {
                if(sibling !== item.parentElement) {
                    sibling.classList.remove('current');
                }
            });
        }
    });
});