<?php
/*
Plugin Name: WP Gitlab Trigger
Description: A plugin to Trigger your GitLab pipeline
Version: 1.0
Author: twinsyn
*/

function gitlab_trigger_settings_menu()
{
    add_options_page(
        'GitLab Trigger',
        'GitLab Trigger',
        'manage_options',
        'gitlab-trigger-settings',
        'gitlab_trigger_page'
    );
}
add_action('admin_menu', 'gitlab_trigger_settings_menu');

function gitlab_trigger_submit()
{
    if (isset($_POST['submit'])) {
        trigger_gitlab_api();
    }
}
add_action('admin_init', 'gitlab_trigger_submit');

function gitlab_token_callback()
{
    $gitlab_token = get_option('gitlab_token');
    echo '<input type="text" name="gitlab_token" value="' . esc_attr($gitlab_token) . '" />';
}

function gitlab_brand_tag_callback()
{
    $brand_tag = get_option('gitlab_brand_tag');
    echo '<input type="text" name="gitlab_brand_tag" value="' . esc_attr($brand_tag) . '" />';
}

function gitlab_project_id_callback()
{
    $project_id = get_option('gitlab_project_id');
    echo '<input type="text" name="gitlab_project_id" value="' . esc_attr($project_id) . '" />';
}

function trigger_gitlab_api()
{
    $gitlab_token = get_option('gitlab_token');
    $brand_tag = get_option('gitlab_brand_tag');
    $project_id = get_option('gitlab_project_id');

    $api_url = 'https://your-gitlab-url/api/v4/projects/' . $project_id . '/trigger/pipeline';

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'PRIVATE-TOKEN: ' . $gitlab_token,
            'Content-Type: application/json',
        ),
        CURLOPT_POSTFIELDS => json_encode(array(
            'ref' => $brand_tag,
        )),
    ));

    curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        echo 'cURL Error: ' . $error;
    } else {
        echo 'API Request Sent Successfully';
    }
}

function gitlab_trigger_register_settings()
{
    add_settings_section(
        'gitlab_trigger_section',
        'GitLab Trigger Settings',
        'gitlab_trigger_section_callback',
        'gitlab-trigger-settings'
    );

    add_settings_field(
        'gitlab_token',
        'GitLab Token',
        'gitlab_token_callback',
        'gitlab-trigger-settings',
        'gitlab_trigger_section'
    );

    add_settings_field(
        'gitlab_brand_tag',
        'Brand/Tag',
        'gitlab_brand_tag_callback',
        'gitlab-trigger-settings',
        'gitlab_trigger_section'
    );

    add_settings_field(
        'gitlab_project_id',
        'Project ID',
        'gitlab_project_id_callback',
        'gitlab-trigger-settings',
        'gitlab_trigger_section'
    );

    register_setting(
        'gitlab_trigger_settings_group',
        'gitlab_token'
    );

    register_setting(
        'gitlab_trigger_settings_group',
        'gitlab_brand_tag'
    );

    register_setting(
        'gitlab_trigger_settings_group',
        'gitlab_project_id'
    );
}
add_action('admin_init', 'gitlab_trigger_register_settings');

function gitlab_trigger_page()
{
?>
    <div class="wrap">
        <h2>GitLab Trigger Settings</h2>
        <form method="post" action="">
            <?php
            settings_fields('gitlab_trigger_settings_group');
            do_settings_sections('gitlab-trigger-settings');
            submit_button('Trigger GitLab');
            ?>
        </form>
    </div>
<?php
}
