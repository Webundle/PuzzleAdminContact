# Puzzle Admin Contact Bundle
**=========================**

Puzzle bundle for managing admin 

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

`composer require webundle/puzzle-admin-contact`

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
{
    $bundles = array(
    // ...

    new Puzzle\Admin\ContactBundle\PuzzleAdminContactBundle(),
                    );

 // ...
}

 // ...
}
```

### Step 3: Register the Routes

Load the bundle's routing definition in the application (usually in the `app/config/routing.yml` file):

# app/config/routing.yml
```yaml
puzzle_admin:
        resource: "@PuzzleAdminContactBundle/Resources/config/routing.yml"
```

### Step 4: Configure Dependency Injection

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Puzzle Client Contact
puzzle_admin_contact:
    title: contact.title
    description: contact.description
    icon: contact.icon
    roles:
        default:
            label: 'ROLE_CONTACT'
            description: contact.role.default
```

### Step 5: Configure navigation module

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Client Admin
puzzle_admin:
    ...
    navigation:
    	nodes:
    		contact:
                label: 'contact.base'
                translation_domain: 'admin'
                attr:
                    class: 'icon-address-book'
                parent: ~
                user_roles: ['ROLE_CONTACT', 'ROLE_ADMIN']
                tooltip: 'contact.tooltip'
            contact_list:
                label: 'contact.base'
                translation_domain: 'admin'
                path: 'admin_contact_list'
                sub_paths: ['admin_contact_create', 'admin_contact_update', 'admin_contact_show']
                parent: contact
                user_roles: ['ROLE_CONTACT', 'ROLE_ADMIN']
                tooltip: 'contact.tooltip'
            contact_group:
                label: 'contact.group.base'
                translation_domain: 'admin'
                path: 'admin_contact_group_list'
                parent: contact
                user_roles: ['ROLE_CONTACT', 'ROLE_ADMIN']
                tooltip: 'contact.group.tooltip'
```