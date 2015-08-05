<div class="large-offset-4 large-4 columns top-20">
    <h3>Please provide your username</h3>
    <?php if(isset($data['error'])): ?>
        <div class="panel callout radius alert">
            <h5>Error:</h5>
            <p>Username is not in the database.</p>
        </div>
    <?php endif; ?>

    <form action="<?=SITE_URL;?>login/generatePass" method="post" name="loginForm" class="top-20">
        <label>Username: <input name="user" type="text"></label>
        <input type="submit" class="button" value="Generate new password" name="newPass">
    </form>


</div>