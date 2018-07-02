# WP YAML Fixtures

A WP-CLI command for describing your WordPress database content in, and importing from, YAML files. Built for testing and scaffolding quickly.

### Getting Started

Install and activate this repo as a plugin.

```
git clone git@github.com:acobster/wp-yaml-fixtures.git ./wp-content/plugins/yaml-fixtures
wp plugin activate yaml-fixtures
```

Set up a YAML file.

```
---
# my-example.yaml
name: My Test Site
description: Just another database described in YAML

users:
- email: admin@example.com
  login: admin
  role: administrator
  password: meep

posts:
- title: My Blog Post
  type: post
  
  content: 'Lorem ipsum dolor sit amet'

  terms:
    category:
    - special

taxonomies:
  category:
  - name: Special
  - name: Snowflake
  - name: Unicorn
...

```

Run the WP-CLI command.

```
wp fixture install my-example.yaml
```

💥 Your database has now been cleaned and populated with fresh data. 💥

### YAML for Days!

Here's a more in-depth example of the kinds of things you can describe in your YAML:

```
---
name: My Test Site
description: Just another database described in YAML

# tell me what **not** to delete
blank_slate:
  preserve_users:
  - me
  - myself
  - i@email.me
  - 123 # ID

# insert users
users:
- email: admin@example.com
  login: admin
  role: administrator
  password: meep
- email: johndoe@example.com
  login: johndoe
  role: editor
  password: mop
  meta:
    my_info: 'This info is v important'
    moar_info: 'here iz sum moar infoz'

# insert arbitrary post data
posts:
- title: My Blog Post
  type: post

- title: Page with a Custom Slug
  slug: yep-this-heres-a-completely-custom-slug
  type: page
  
  content: |
    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
    Nunc haec primum fortasse audientis servire debemus.

    Sumenda potius quam expetenda.

    Mihi quidem Homerus huius modi quiddam vidisse videatur in iis,
    quae de Sirenum cantibus finxerit.
    Ut in geometria, prima si dederis, danda sunt omnia.

  # specify any number of terms, across taxonomies
  terms:
    page_type:
    - custom-term-slug
    category:
    - special

- title: A Post with Some Meta Fields
  type: page
  # oh and I want to nest this page
  parent: bar
  
  # specify arbitrary meta fields
  meta:
    my_custom_field: Some custom value

    # specify multiple values for the same key!
    # these will be inserted as three wp_postmeta rows
    another_field:
    - 'value #1'
    - 'value #2'
    - 'value #3'

  terms:
    page_type:
    - custom-term-slug
    - another
    - and-another

# Define taxonomy terms
taxonomies:
  category:
  - name: Special
  - name: Snowflake
  - name: Unicorn
  
  page_type:
  - name: Page Type
    slug: custom-term-slug
  - name: Another Page Type
    slug: another
  - name: And Another
...
```

## License

MIT
