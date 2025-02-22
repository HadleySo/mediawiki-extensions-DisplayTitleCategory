[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)

# Display Title Category 

The **Display Title Category** extension appends and/or prepends text to a page's display title based on the page's categories. Pages are saved with the modified display title. Can be paired with [Display Title](https://www.mediawiki.org/wiki/Extension:Display_Title) to have the display title to be used as the default link text in links to the page.

Page display titles are only modified when a full parse is run on the page, changing page permissions or moving a page does not update the page display title.

Uses [Hooks/MultiContentSave](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/MultiContentSave) and calls `setDisplayTitle()` to modify the display title before a page is saved.

## Installation
- Inside your `extensions/` directory download this repository: `git clone --depth 1 --branch 1.1.0 https://github.com/HadleySo/mediawiki-extensions-DisplayTitleCategory.git DisplayTitleCategory`
- Add the following code at the bottom of your `LocalSettings.php` file: `wfLoadExtension( 'DisplayTitleCategory' );`
- Configure as required
- Navigate to Special:Version on your wiki to verify that the extension is successfully installed

## Configuration 

| Configuration flag                     | Default value | Description                                                                             |
|----------------------------------------|---------------|-----------------------------------------------------------------------------------------|
| $wgDisplayTitleCategoryLabels          | []            | A 2D nested array of category names and values to prepend or append                     |
| $wgDisplayTitleCategoryPrepend         | true          | Whether to prepend onto the title                                                       |
| $wgDisplayTitleCategoryAppend          | false         | Whether to append onto the title                                                        |
| $wgDisplayTitleCategoryDisplayTitle    | false         | If [Display Title](https://www.mediawiki.org/wiki/Extension:Display_Title) is installed |

If [Display Title](https://www.mediawiki.org/wiki/Extension:Display_Title) is also installed, it is recommended to set `$wgRestrictDisplayTitle = false;`.

Category names must be in DB Key format, where spaces are replaced with underscores `_`. 
Declare multiple labels like this:

```
$wgDisplayTitleCategoryLabels['Hardcover_Books']  = 'Books -';
$wgDisplayTitleCategoryLabels['Old_Aircraft']  = '(A)';
```