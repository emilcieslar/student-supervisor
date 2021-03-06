<div class="large-offset-4 large-4 columns top-20">
    <h2>Log in</h2>
    <?php if(isset($data['error'])): ?>
        <div class="panel callout radius alert">
            <h5>Error:</h5>
            <p>Please provide correct details.</p>
        </div>
    <?php endif; ?>

    <a class="clearfix" href="<?=SITE_URL;?>login/forgotPassword">Forgot password?</a>

    <form action="<?=SITE_URL;?>login/loginPost" method="post" name="loginForm" class="top-20">
        <label>Username: <input name="user" type="text"></label>
        <label>Password: <input name="pass" type="password"></label>
        <input type="submit" class="button" value="Log in" name="logIn">
        <a href="<?=$data['link']?>" class="button success">Sign in using Google &rarr;</a>
    </form>


</div>