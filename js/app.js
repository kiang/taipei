$(function () {
    "use strict";

    /* Sidebar tree view */
    $(".sidebar .treeview").tree();

    /* 
     * Make sure that the sidebar is streched full height
     * ---------------------------------------------
     * We are gonna assign a min-height value every time the
     * wrapper gets resized and upon page load. We will use
     * Ben Alman's method for detecting the resize event.
     * 
     **/
    function _fix() {
        //Get window height and the wrapper height
        var height = $(window).height() - $("body > .header").height() - ($("body > .footer").outerHeight() || 0);
        $(".wrapper").css("min-height", height + "px");
        var content = $(".wrapper").height();
        //If the wrapper height is greater than the window
        if (content > height)
            //then set sidebar height to the wrapper
            $(".left-side, html, body").css("min-height", content + "px");
        else {
            //Otherwise, set the sidebar to the height of the window
            $(".left-side, html, body").css("min-height", height + "px");
        }
    }
//Fire upon load
    _fix();
//Fire when wrapper is resized
    $(".wrapper").resize(function () {
        _fix();
        fix_sidebar();
    });

//Fix the fixed layout sidebar scroll bug
    fix_sidebar();
})

        /*
         * SIDEBAR MENU
         * ------------
         * This is a custom plugin for the sidebar menu. It provides a tree view.
         * 
         * Usage:
         * $(".sidebar).tree();
         * 
         * Note: This plugin does not accept any options. Instead, it only requires a class
         *       added to the element that contains a sub-menu.
         *       
         * When used with the sidebar, for example, it would look something like this:
         * <ul class='sidebar-menu'>
         *      <li class="treeview active">
         *          <a href="#>Menu</a>
         *          <ul class='treeview-menu'>
         *              <li class='active'><a href=#>Level 1</a></li>
         *          </ul>
         *      </li>
         * </ul>
         * 
         * Add .active class to <li> elements if you want the menu to be open automatically
         * on page load. See above for an example.
         */
                (function ($) {
                    "use strict";

                    $.fn.tree = function () {

                        return this.each(function () {
                            var btn = $(this).children("a").first();
                            var menu = $(this).children(".treeview-menu").first();
                            var isActive = $(this).hasClass('active');

                            //initialize already active menus
                            if (isActive) {
                                menu.show();
                                btn.children(".fa-angle-left").first().removeClass("fa-angle-left").addClass("fa-angle-down");
                            }
                            //Slide open or close the menu on link click
                            btn.click(function (e) {
                                e.preventDefault();
                                if (isActive) {
                                    //Slide up to close menu
                                    menu.slideUp();
                                    isActive = false;
                                    btn.children(".fa-angle-down").first().removeClass("fa-angle-down").addClass("fa-angle-left");
                                    btn.parent("li").removeClass("active");
                                } else {
                                    //Slide down to open menu
                                    menu.slideDown();
                                    isActive = true;
                                    btn.children(".fa-angle-left").first().removeClass("fa-angle-left").addClass("fa-angle-down");
                                    btn.parent("li").addClass("active");
                                }
                            });

                            /* Add margins to submenu elements to give it a tree look */
                            menu.find("li > a").each(function () {
                                var pad = parseInt($(this).css("margin-left")) + 10;

                                $(this).css({"margin-left": pad + "px"});
                            });

                        });

                    };


                }(jQuery));