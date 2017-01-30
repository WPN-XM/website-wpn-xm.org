
    <!-- Google Analytics -->
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-26811143-1']);
      _gaq.push(['_trackPageview']);
      (function () {
          var ga = document.createElement('script');
          ga.type = 'text/javascript';
          ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0];
          s.parentNode.insertBefore(ga, s);
      })();
    </script>
    <!-- End Google Analytics -->

    <!-- Google Tag Manager
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5FQQ8G"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5FQQ8G');</script>-->
    <!-- End Google Tag Manager -->

    <!-- Javascripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script>
    <![endif]-->

    <script type="text/javascript">
      // this is a ScollTo() function with an additional scrolling offset
      $(".navbar li a[href^='#']").on('click', function (event) {
          var target = this.hash;
          event.preventDefault();
          var navOffset = $('#navbar').height() + 72;

          return $('html, body').animate({
              scrollTop: $(this.hash).offset().top - navOffset
          }, 1400, function () {
              return window.history.pushState(null, null, target);
          });
      });

      $(document).ready(function () {
          // fade-in/fade-out of the brand logo in the top-navbar
          $(window).scroll(function(){
              var logo = $('a.navbar-brand img');
              if ($(this).scrollTop() > 210 && logo.not(':visible')) {
                  logo.fadeIn(400);
              } else {
                  logo.stop().fadeOut(400);
              }
          });
      });
    </script>

    <!-- cookiechoices.org -->
    <script src="js/cookiechoices/cookiechoices.min.js"></script>
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function(event) {
        cookieChoices.showCookieConsentBar('This site uses cookies. By continuing to use this site, you are agreeing to our use of cookies.', 'Got it!');
      });
    </script>
