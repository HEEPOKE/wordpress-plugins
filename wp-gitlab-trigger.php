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

function gitlab_trigger_submit()
{
    if (isset($_POST['submit'])) {
        trigger_gitlab_api();
    }
}

function gitlab_save_submit()
{
    if (isset($_POST['save_data'])) {
        save_settings();
    }
}

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

function save_settings()
{
    $gitlab_token = isset($_POST['gitlab_token']) ? sanitize_text_field($_POST['gitlab_token']) : get_option('gitlab_token');
    $brand_tag = isset($_POST['gitlab_brand_tag']) ? sanitize_text_field($_POST['gitlab_brand_tag']) : get_option('gitlab_brand_tag');
    $project_id = isset($_POST['gitlab_project_id']) ? sanitize_text_field($_POST['gitlab_project_id']) : get_option('gitlab_project_id');

    update_option('gitlab_token', $gitlab_token);
    update_option('gitlab_brand_tag', $brand_tag);
    update_option('gitlab_project_id', $project_id);
}

function trigger_gitlab_api()
{
    $gitlab_token = get_option('gitlab_token');
    $brand_tag = get_option('gitlab_brand_tag');
    $project_id = get_option('gitlab_project_id');

    if (!empty($gitlab_token)) {
        $api_url = "https://gitlab.com/api/v4/projects/{$project_id}/trigger/pipeline?ref={$brand_tag}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'PRIVATE-TOKEN: ' . $gitlab_token
            )
        );

        $response = array(
            'response' => curl_exec($ch),
            'error' => curl_error($ch)
        );

        echo json_encode($response);

        curl_close($ch);
    } else {
        echo "GitLab token is missing or empty.";
    }
}

function gitlab_trigger_section_callback()
{
    echo '<p>Welcome to the GitLab Trigger settings section. Configure the plugin settings below:</p>';
    echo '<ul>';
    echo '<li><strong>GitLab Token:</strong> Enter your GitLab access token for authentication.</li>';
    echo '<li><strong>Brand or Tag:</strong> Specify the brand or tag triggering the pipeline.</li>';
    echo '<li><strong>Project ID:</strong> Provide the project ID for pipeline triggering.</li>';
    echo '</ul>';
}

function gitlab_trigger_register_settings()
{
    add_settings_section(
        'gitlab_trigger_section',
        'GitLab Trigger Pipeline',
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
        'Brand or Tag',
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

function gitlab_trigger_page()
{
    ?>
    <div class="wrap">
        <form method="post" action="">
            <?php
            settings_fields('gitlab_trigger_settings_group');
            do_settings_sections('gitlab-trigger-settings');
            gitlab_save_submit();
            submit_button('Save', 'primary', 'save-data');
            submit_button('Test Pipeline', 'primary', 'submit');
            save_settings();
            ?>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const projectInput = document.querySelector('input[name="gitlab_project_id"]');

            projectInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
    <?php
}

add_action('admin_menu', 'gitlab_trigger_settings_menu');
add_action('admin_init', 'gitlab_save_submit');
add_action('admin_init', 'gitlab_trigger_submit');
add_action('admin_init', 'gitlab_trigger_register_settings');
add_action('save_post', 'trigger_gitlab_api');