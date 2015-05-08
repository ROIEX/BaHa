<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=1024, maximum-scale=1, user-scalable=1">

    <title>Bridge Haul</title>

    <link href="<?=SCRIPT_PATH_ROOT?>styles/base.css" rel="stylesheet">

    <script src="<?=SCRIPT_PATH_ROOT?>js/jquery-1.10.2.min.js"></script>
    <script src="<?=SCRIPT_PATH_ROOT?>js/base.js"></script>

    <script src="<?=SCRIPT_PATH_ROOT?>js/magnific-popup/jquery.magnific-popup.min.js"></script>
    <link href="<?=SCRIPT_PATH_ROOT?>js/magnific-popup/magnific-popup.css" rel="stylesheet">
    <script src="<?=SCRIPT_PATH_ROOT?>js/jquery.maskedinput.min.js"></script>
    

</head>

<body>

    <div class="main-block">
        <div class="header">
            <div class="topmenu <?=($shortHeader == "Y"||$_SESSION['uid'])?'topmenu2':''?>">
                <ul>
                    <?if(!$_SESSION['uid']):?>
                        <li><a href="#">SOLUTIONS</a>
                            <ul>
                                <li><a href="#" class="underline">Carrier Solutions</a></li>
                                <li><a href="#">Carrier Fleet Managment</a></li>
                                <li><a href="#">Owner Operator Tools</a></li>
                                <li><a href="#" class="underline newsubblock">Shipper Solutions</a></li>
                                <li><a href="#">Shipper Freight Procurement</a></li>                        
                            </ul>
                        </li>
                        <li><a href="#">SHIPPERS</a></li>
                        <li><a href="#">CARRIERS</a></li>
                        <li><a href="#">ABOUT</a></li>
                        <li><a href="#">CONTACT</a></li>
                        <li>
                            <a href="#login" class="lightbox-inline-open">LOGIN</a>
                        </li>
                    <?else:?>
                        <li class="left"><a class="au_logo" href="<?=SITE.SCRIPT_PATH_ROOT?>"><img src="<?=SITE.SCRIPT_PATH_ROOT?>images/logo.png" width=60 height=27></a></li>
                        <li class="left"><a href="#">Manage users</a></li>
                        <li class="left"><a href="#">Account info</a></li>

                        <li><a href="#">Summary</a></li>
                        <li><a href="drivers.php">Drivers</a></li>
                        <li><a href="#">HoS</a></li>
                        <li><a href="#">DVIR</a></li>
                        <li><a href="#">Loads</a></li>
                        <li><a href="#">Performance</a></li>
                        <li><a href="#">Profile</a></li>
                        <li>
                            <a href="#"><?=$_SESSION['email']?></a>
                        </li>
                        <li>
                            <a href="logout.php">logout</a>
                        </li>
                    <?endif?>                
                </ul>
            </div>
            <?if($shortHeader != "Y" || !$_SESSION['uid']):?>
                <div class="topbanner">
                    <div class="left-bg">
                        <a href="<?=SCRIPT_PATH_ROOT?>" class="logo"></a>
                    </div>
                    <span class="slogan">Bridging the gap between carriers and shippers</span>
                </div>
                <div class="medialine">
                    <a href="#" class="how">How it works</a>
                    <a href="#signup1" class="registrate lightbox-inline-open">Sign up it's free</a>
                </div>
            <?endif?>
        </div><!--.header end-->

        <div class="content <?=($dashboardContentModifier == "Y")?'dashboard-content':''?>">