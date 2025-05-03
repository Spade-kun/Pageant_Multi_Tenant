// Function to update text colors based on background colors
function updateTextColors() {
    function isLightColor(color) {
        const hex = color.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return brightness > 155;
    }

    // Get the elements
    const logoHeader = $('.logo-header');
    const navbar = $('.navbar-header');
    const sidebar = $('.sidebar');

    // Apply color classes based on background colors
    if (isLightColor('{{ $uiSettings->logo_header_color }}')) {
        // If background is light, use dark text
        logoHeader.addClass('text-dynamic-dark').removeClass('text-dynamic-light');
        $('.brand-text').css('color', '#000000');
    } else {
        // If background is dark, use light text
        logoHeader.addClass('text-dynamic-light').removeClass('text-dynamic-dark');
        $('.brand-text').css('color', '#ffffff');
    }

    if (isLightColor('{{ $uiSettings->navbar_color }}')) {
        // If background is light, use dark text
        navbar.addClass('text-dynamic-dark').removeClass('text-dynamic-light');
        $('.profile-username').css('color', '#000000');
        $('.profile-username .op-7').css('color', '#000000');
        $('.profile-username .fw-bold').css('color', '#000000');
    } else {
        // If background is dark, use light text
        navbar.addClass('text-dynamic-light').removeClass('text-dynamic-dark');
        $('.profile-username').css('color', '#ffffff');
        $('.profile-username .op-7').css('color', '#ffffff');
        $('.profile-username .fw-bold').css('color', '#ffffff');
    }

    if (isLightColor('{{ $uiSettings->sidebar_color }}')) {
        // If background is light, use dark text
        sidebar.addClass('text-dynamic-dark').removeClass('text-dynamic-light');
        $('.sidebar .nav-item p').css('color', '#000000');
        $('.sidebar .nav-item i').css('color', '#000000');
    } else {
        // If background is dark, use light text
        sidebar.addClass('text-dynamic-light').removeClass('text-dynamic-dark');
        $('.sidebar .nav-item p').css('color', '#ffffff');
        $('.sidebar .nav-item i').css('color', '#ffffff');
    }
}

// Initialize when document is ready
$(document).ready(function () {
    // Initialize scrollbars
    $('.scrollbar-inner').scrollbar();

    // Function to update layout based on sidebar position
    function updateLayoutForSidebarPosition(position) {
        if (position === 'right') {
            $('.main-panel').css({
                'float': 'left',
                'margin-right': $('.sidebar').width() + 'px',
                'margin-left': '0'
            });
            $('.custom-template').css({
                'left': '0',
                'right': 'auto'
            });
        } else {
            $('.main-panel').css({
                'float': '',
                'margin-right': '',
                'margin-left': ''
            });
            $('.custom-template').css({
                'left': '',
                'right': ''
            });
        }
    }

    // Initial layout update
    updateLayoutForSidebarPosition('{{ $uiSettings->sidebar_position }}');

    // Update layout when sidebar is toggled
    $('.toggle-sidebar').click(function () {
        $('.wrapper').toggleClass('sidebar-collapse');
        $(this).find('i').toggleClass('gg-menu-right gg-menu-left');

        // Adjust margins when sidebar is collapsed
        if ($('.wrapper').hasClass('sidebar-collapse')) {
            if ('{{ $uiSettings->sidebar_position }}' === 'right') {
                $('.main-panel').css('margin-right', '75px');
            }
        } else {
            if ('{{ $uiSettings->sidebar_position }}' === 'right') {
                $('.main-panel').css('margin-right', '250px');
            }
        }
    });

    // Mobile sidebar toggle
    $('.sidenav-toggler').click(function () {
        $('.wrapper').toggleClass('nav-toggle');
        $(this).find('i').toggleClass('gg-menu-left gg-menu-right');
    });

    // Settings panel toggle
    $('.custom-toggle').click(function () {
        $('.custom-template').toggleClass('active');
    });

    // Apply color changes from settings panel
    $('.changeLogoHeaderColor').click(function () {
        if ($(this).attr('data-color') != null) {
            $('.logo-header').attr('data-background-color', $(this).attr('data-color'));
            updateTextColors(); // Update text colors when logo header color changes
        }
        $(this).parent().find('.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $('.changeTopBarColor').click(function () {
        if ($(this).attr('data-color') != null) {
            $('.navbar-header').attr('data-background-color', $(this).attr('data-color'));
            updateTextColors(); // Update text colors when navbar color changes
        }
        $(this).parent().find('.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $('.changeSideBarColor').click(function () {
        if ($(this).attr('data-color') != null) {
            $('.sidebar').attr('data-background-color', $(this).attr('data-color'));
            updateTextColors(); // Update text colors when sidebar color changes
        }
        $(this).parent().find('.selected').removeClass('selected');
        $(this).addClass('selected');
    });


    // Call the function on document ready
    updateTextColors();

    // Add event listener for color changes
    $(document).on('colorChanged', function () {
        updateTextColors();
    });
}); 