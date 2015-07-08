<div class="large-offset-4 large-4 columns top-20">
    <h2>Log in</h2>
    <?php if(isset($data['error'])): ?>
        <div class="panel callout radius alert">
            <h5>Error:</h5>
            <p>Please provide correct details.</p>
        </div>
    <?php endif; ?>
    <form action="<?=SITE_URL;?>login/loginPost" method="post" name="loginForm">
        <input type="hidden" name="action">
        <label>Username: <input name="user" type="text"></label>
        <label>Password: <input name="pass" type="password"></label>
        <input type="submit" class="button" value="Log in" name="logIn">
    </form>
</div>