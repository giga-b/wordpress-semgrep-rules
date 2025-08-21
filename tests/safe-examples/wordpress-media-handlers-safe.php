<?php
/**
 * Safe WordPress Media Handler Examples
 * 
 * This file demonstrates safe usage of WordPress media handling functions
 * that are secure by design and handle validation internally.
 */

// Safe: media_handle_upload with proper post context
function safe_media_upload_handler() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['upload_file'];
    $post_id = get_the_ID();
    
    // media_handle_upload handles validation internally
    $attachment_id = media_handle_upload('upload_file', $post_id);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    return $attachment_id;
}

// Safe: wp_handle_sideload with proper post context
function safe_sideload_handler() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['sideload_file'];
    $post_id = get_the_ID();
    
    // wp_handle_sideload handles validation internally
    $attachment_id = wp_handle_sideload($file, $post_id);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    return $attachment_id;
}

// Safe: Conditional media handling
function safe_conditional_media_upload() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['conditional_file'];
    $post_id = get_the_ID();
    
    // Conditional usage is safe
    if (media_handle_upload('conditional_file', $post_id)) {
        return true;
    }
    
    return false;
}

// Safe: Conditional sideload handling
function safe_conditional_sideload() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['conditional_sideload'];
    $post_id = get_the_ID();
    
    // Conditional usage is safe
    if (wp_handle_sideload($file, $post_id)) {
        return true;
    }
    
    return false;
}

// Safe: Media handling with additional validation
function safe_media_with_extra_validation() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['extra_validation_file'];
    $post_id = get_the_ID();
    
    // Additional size validation
    if ($file['size'] > wp_max_upload_size()) {
        return false;
    }
    
    // media_handle_upload handles type validation internally
    $attachment_id = media_handle_upload('extra_validation_file', $post_id);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    return $attachment_id;
}

// Safe: Sideload with additional validation
function safe_sideload_with_extra_validation() {
    if (!current_user_can('upload_files')) {
        return false;
    }
    
    $file = $_FILES['extra_validation_sideload'];
    $post_id = get_the_ID();
    
    // Additional size validation
    if ($file['size'] > wp_max_upload_size()) {
        return false;
    }
    
    // wp_handle_sideload handles type validation internally
    $attachment_id = wp_handle_sideload($file, $post_id);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    return $attachment_id;
}
?>
