// Function to determine if text should be black or white based on background color
function getContrastYIQ(hexcolor) {
    hexcolor = hexcolor.replace('#', '');
    if (hexcolor.length === 3) {
        hexcolor = hexcolor[0] + hexcolor[0] + hexcolor[1] + hexcolor[1] + hexcolor[2] + hexcolor[2];
    }
    var r = parseInt(hexcolor.substr(0, 2), 16);
    var g = parseInt(hexcolor.substr(2, 2), 16);
    var b = parseInt(hexcolor.substr(4, 2), 16);
    var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? '#000000' : '#ffffff';
}

function updateTextColors() {
    // Get the elements
    const logoHeader = $('.logo-header');
    const navbar = $('.navbar-header');
    const sidebar = $('.sidebar');

    // Get background colors from data attribute or style
    let logoHeaderColor = logoHeader.attr('data-background-color') || logoHeader.css('background-color');
    let navbarColor = navbar.attr('data-background-color') || navbar.css('background-color');
    let sidebarColor = sidebar.attr('data-background-color') || sidebar.css('background-color');

    // If color is in rgb format, convert to hex
    function rgbToHex(rgb) {
        if (!rgb) return '#ffffff';
        if (rgb[0] === '#') return rgb;
        var result = rgb.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        return result ? "#" + (
            (1 << 24) + (parseInt(result[1]) << 16) + (parseInt(result[2]) << 8) + parseInt(result[3])
        ).toString(16).slice(1) : '#ffffff';
    }
    logoHeaderColor = rgbToHex(logoHeaderColor);
    navbarColor = rgbToHex(navbarColor);
    sidebarColor = rgbToHex(sidebarColor);

    // Set text color for logo header
    const logoTextColor = getContrastYIQ(logoHeaderColor);
    $('.brand-text').css('color', logoTextColor);

    // Set text color for navbar
    const navbarTextColor = getContrastYIQ(navbarColor);
    $('.profile-username').css('color', navbarTextColor);
    $('.profile-username .op-7').css('color', navbarTextColor);
    $('.profile-username .fw-bold').css('color', navbarTextColor);

    // Set text color for sidebar
    const sidebarTextColor = getContrastYIQ(sidebarColor);
    $('.sidebar .nav-item p').css('color', sidebarTextColor);
    $('.sidebar .nav-item i').css('color', sidebarTextColor);
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