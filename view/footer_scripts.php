
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

    <!-- Javascripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
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

      // this is the fade-in-fade-out of the mini logi in the top-navbar
      $(document).ready(function () {
          $(window).scroll(function(){
              var scrollTop = $(this).scrollTop();
              if (scrollTop > 210) {
                  $('a.navbar-brand img').fadeIn(400);
              } else {
                  $('a.navbar-brand img').stop().fadeOut(400);
              }
          });
      });
    </script>
