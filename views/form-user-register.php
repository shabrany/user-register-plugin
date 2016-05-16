<div class="form-user-register">
    
    <h3>Gebruiker register</h3>    
    
    <?php if (!empty($message)): ?>
    <div class="error" style="color: red;">        
        <?php echo $message; ?>        
    </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
        <?php wp_nonce_field( 'front_user_meta_box_nonce', 'meta_box_nonce' ); ?>
        <p class="field">
            <label>Naam</label>            
            <input type="text" name="<?php echo $prefix . 'name' ?>" value="<?php echo (isset($_POST[$prefix . 'name'])) ? $_POST[$prefix . 'name'] : ''; ?>">            
        </p>
            
        
        <?php foreach($fields as $field): ?>
            <?php $meta_value = isset($_POST[$prefix . $field['name']]) ? $_POST[$prefix . $field['name']] : ''; ?>
        
            <?php if ($field['name'] == 'saved_in_page'): ?>
                <input type="hidden" name="<?php echo $prefix . $field['name']; ?>" value="<?php echo $post->post_title; ?>">
            <?php else: ?>
        
                <p class="field">
                    <label><?php echo $field['label']; ?></label>

                    <?php if ($field['type'] == 'select'): ?>

                        <select name="<?php echo $prefix . $field['name']; ?>">
                            <?php foreach($field['values'] as $key => $value): ?>
                                <?php $selected = ($meta_value == $key) ? 'selected' : ''; ?>
                                <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>

                    <?php else: ?>

                        <input type="text" name="<?php echo $prefix . $field['name']; ?>" value="<?php echo $meta_value; ?>">

                    <?php endif; ?>

                </p>
            
            <?php endif; ?>
        <?php endforeach; ?>

        <p>
            <input type="submit" value="Verzenden">
        </p>
    </form>
</div>
