<?php foreach($fields as $field): ?>
    <?php $meta_value = get_post_meta($post->ID, $prefix . $field['name'], true); ?>
    <div>
        <label><?php echo $field['label']; ?></label>
        <div>
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
        </div>
    </div>    
<?php endforeach; ?>