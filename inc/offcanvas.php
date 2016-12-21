<div id="offcanvas" class="uk-offcanvas">
    <div class="uk-offcanvas-bar">
        <ul class="uk-nav uk-nav-offcanvas">
            <li class="uk-active">
                <a href="index.php">Início</a>
            </li>
            <li>
                <a href="advanced_search.php">Busca avançada</a>
            </li>
            <li>
                <a href="contato.php">Contato</a>
            </li>
            <?php if(empty($_SESSION['oauthuserdata'])){ ?>
                <li><a href="aut/oauth.php">Login</a></li>
            <?php } else { ?>
                <li><a href="aut/logout.php">Logout</a></li>
            <?php } ?>
            <li>
                <a href="about.php">Sobre</a>
            </li>
        </ul>
    </div>
</div>

<!-- ###### Script para criar o pop-up do popterms ###### -->
<script>
    function creaPopup(url)
    {
      tesauro=window.open(url,
      "Tesauro",
      "directories=no, menubar =no,status=no,toolbar=no,location=no,scrollbars=yes,fullscreen=no,height=600,width=450,left=500,top=0"
      )
    }
 </script> 