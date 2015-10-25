<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="css/style.css"></link>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
        <title>Instant DBLP Search</title>
    </head>
    <body>
        <fieldset class='fset'>
            <div style="font-size: 22px">
                <center> <img src="dblp_logo.png"> </center>
        </div>
            <div style="font-size: 18px; margin: 0px 0px 5px 20px; font-weight: bold;">
                Search by title, author or journal...
            </div>   
        <div>
            <form name="search" method="post" action="" onsubmit="return form_validate();">
                <center><input type='text' class="textbox" onkeyup="return auto_complete(this.value);" autofocus="true" name="query" id="query">
                    <input type="submit" name="search" value="Search" class="button"></center>
                <input type="hidden" name="indexType" id="indexType" value="2" />                        
            </form>
        </div>
        </fieldset>
        <fieldset class='fset' id="place_holder" style="height: auto; border-style: hidden;"></fieldset>
    </body>
</html>
