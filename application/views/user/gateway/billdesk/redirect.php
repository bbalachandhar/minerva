<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo $this->setting[0]['name']; ?></title>
    </head>
    <body>
        <?php if (isset($api_error)) {
            echo "<div class='alert alert-danger'>" . $api_error . "</div>";
        }
        ?>

        <p>Please wait, your order is being processed and you will be redirected to the billdesk website.</p>

        <form name="sdklaunch" id="sdklaunch" action="<?php echo $form_action; ?>" method="POST">
            <?php
            if (isset($fields)) {
                foreach ($fields as $key => $value) {
                    echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
                }
            }
            ?>
            <input name='btnSubmit' type='submit' value='Complete your Payment' id="submitBtn" style="display:none;" />
        </form>
        <script>
            console.log("Attempting auto-redirect...");
            try {
                document.getElementById("sdklaunch").submit();
            } catch (e) {
                console.error("Auto-redirect failed, showing button.", e);
                document.getElementById("submitBtn").style.display = 'block';
            }

            window.onload = function() {
                document.getElementById("sdklaunch").submit();
            };
            
            // Show button after 3 seconds if redirect hasn't happened
            setTimeout(function() {
                document.getElementById("submitBtn").style.display = 'block';
            }, 3000);
        </script>
    </body>
</html>
