<?php
/**
 * Documentation admin page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>WDTA Membership Documentation</h1>
    
    <h2>Hi Tina,</h2>

    <p>Available placeholders for emails: <code>{user_name}</code>, <code>{user_email}</code>, <code>{year}</code>, <code>{amount}</code>, <code>{deadline}</code>, <code>{renewal_url}</code>, <code>{site_name}</code>. These can be used in any of the membership emails to personalise the content.</p>

    <h2>Membership Signup Process</h2>

    <ol>
        <li>
            <strong>Expression of Interest:</strong> A potential new member fills in the <em>Expression of Interest</em> form in Formidable Forms.
        </li>
        <li>
            <strong>Approval:</strong> If approved, you manually create the new user by clicking <a href="https://www.wdta.org.au/wp-admin/user-new.php">here</a>.
        </li>
        <li>
            <strong>Login and Membership Form:</strong> The new user can then go to <a href="https://www.wdta.org.au/new-membership-form/">New Membership Form</a>. They must login first. This prevents people from signing up unless they have been approved in step 2.
        </li>
        <li>
            <strong>Payment Options:</strong> They can pay for the current year or the next year. Note: The next year option will only appear from November of the current year.
        </li>
        <li>
            <strong>Emails:</strong> All emails are editable, and you can define who gets a copy of each email.
        </li>
        <li>
            <strong>Member-only Pages:</strong> Any pages you want to be available <em>only</em> to members can be selected in the settings: <a href="https://www.wdta.org.au/wp-admin/admin.php?page=wdta-settings">WDTA Settings</a>.
        </li>
        <li>
            <strong>Customisation:</strong> All of this workflow can be changed through the GitHub repository if you want to adjust how it works.
        </li>
    </ol>
</div>
