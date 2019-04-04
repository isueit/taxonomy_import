# Taxonomy Import
This module creates taxonomy vocabularies from text files in Drupal 8. This is an updated version of [taxonomy_import](https://github.com/bwebster719/taxonomy_import) for Drupal 7.

## Use
On enable, if the setting 'auto_create_counties_in_iowa' is true, the module will automatically create the counties_in_iowa taxonomy and populate it with terms.

After enabling, go to admin > config > Content Authoring > Taxonomy Import
This will allow you to create a new taxonomy by entering the relative or absolute location of your text file.
The default file contains a list of the Counties in Iowa and can be found in [/src/data/IowaCounties.txt](/src/data/IowaCounties.txt).
