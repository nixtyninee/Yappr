<head>
    <title>Yappr</title>
    <link href="css/styles.css" rel="stylesheet">

    <link href="https://getbootstrap.com/1.4.0/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://getbootstrap.com/1.4.0/assets/css/docs.css" rel="stylesheet">
    <link href="https://getbootstrap.com/1.4.0/assets/js/google-code-prettify/prettify.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.5.2.min.js"></script>
    <script src="https://getbootstrap.com/1.4.0/assets/js/jquery/jquery.tablesorter.min.js"></script>
    <script src="https://getbootstrap.com/1.4.0/assets/js/google-code-prettify/prettify.js"></script>
    <script>$(function () { prettyPrint() })</script>
    <script src="https://getbootstrap.com/1.4.0/assets/js/bootstrap-dropdown.js"></script>
    <script src="https://getbootstrap.com/1.4.0/assets/js/bootstrap-twipsy.js"></script>
    <script src="https://getbootstrap.com/1.4.0/assets/js/bootstrap-scrollspy.js"></script>
    <script src="https://getbootstrap.com/1.4.0/assets/js/application.js"></script>

    <style type="text/css">
      /* Override some defaults */
      html, body {
        background-color: #eee;
      }
      body {
        padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
      }
      .container > footer p {
        text-align: center; /* center align it with the container */
      }
      .container {
        width: 820px; /* downsize our container to make the content feel a bit tighter and more cohesive. NOTE: this removes two full columns from the grid, meaning you only go to 14 columns and not 16. */
      }

      /* The white background content wrapper */
      .container > .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
        -webkit-border-radius: 0 0 6px 6px;
           -moz-border-radius: 0 0 6px 6px;
                border-radius: 0 0 6px 6px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }

      /* Page header tweaks */
      .page-header {
        background-color: #f5f5f5;
        padding: 20px 20px 10px;
        margin: -20px -20px 20px;
      }

      /* Styles you shouldn't keep as they are for displaying this base example only */
      .content .span10,
      .content .span4 {
        min-height: 500px;
      }
      /* Give a quick and non-cross-browser friendly divider */
      .content .span4 {
        margin-left: 0;
        padding-left: 19px;
        border-left: 1px solid #eee;
      }

      .topbar .btn {
        border: 0;
      }

    </style>

</head>
<body>
 <div class="topbar">
      <div class="fill">
        <div class="container">
          <h3><a href="index.php">yappr</a></h3>
          <ul>
          <?php if (isset($_SESSION['loggedin'])): ?>
            <li><a href="user.php?username=<?php echo $_SESSION['username']; ?>">@<?php echo $_SESSION['username']; ?></a></li>
        <?php else: ?>
        <?php endif; ?>
            <li><a href="index.php">home</a></li>
            <?php if (isset($_SESSION['loggedin'])): ?>
            <li><a href="logout.php">logout</a></li>
        <?php else: ?>
            <li><a href="login.php">login</a></li>
            <li><a href="register.php">register</a></li>
        <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>