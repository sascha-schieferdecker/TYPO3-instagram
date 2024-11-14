# Show a profile Instagram feed in TYPO3

# Based On https://github.com/in2code-de/instagram

## Introduction

You need to setup two scrapers at apify.com and use the following endpoints:

apify/instagram-post-scraper -> https://api.apify.com/v2/acts/apify~instagram-scraper/runs/last/dataset/items?token=***

These URLs show the result of the last run. Make sure you run the scheduler part of the extension often enough you do not miss any posts.


## Installation

`composer require saschaschieferdecker/instagram`


## Configuration

### The instagram part

Sign up to apify and create two scrapers and set them up for the profiles to scrape:

apify/instagram-scraper

Make sure you don't select any filter (posts/reels) while setting up the scraper.
It is okay if you scrape multiple profiles

### CLI commands

#### Import Feed

If you have access to the instagram API (look at the FlexForm in the plugin and watch for the green message), you can
import images via CLI or scheduler.

Import the latest posts and reels:

```bash
./vendor/bin/typo3 instagram:importfeed  \
 https://api.apify.com/v2/acts/apify~instagram-scraper/runs/last/dataset/items?token=xxx \
 xxx@yyy.com
 ```



### Scheduler

#### Import images

Add a new scheduler task of type `Execute console commands (scheduler)` and select `instagram:importfeed`. Now you can
add a frequency (e.g. `*/30 * * * *` for every 30 minutes), a instagram username and one (or more) email address if
error happens (and you want get notified).

| Field    | Description                                                                                                                              |
|----------|------------------------------------------------------------------------------------------------------------------------------------------|
| notify   | Optional: Get notified via email if a CURL error occurs (e.g. if instagram blocks your requests). Commaseparated email list is provided. |
| post-url   | Apify Endpoint for Posts                                                                                                                 |

### HTML output modification

Overwrite and modify the HTML output:

```
plugin {
    tx_instagram_pi1 {
        view {
            templateRootPaths {
                0 = EXT:instagram/Resources/Private/Templates/
            }
        }
    }
}
```


Example html:

```
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
	  xmlns:instagram="http://typo3.org/ns/In2code/Instagram/ViewHelpers"
	  data-namespace-typo3-fluid="true">

<div class="c-socialwall">
	<div class="c-socialwall">
		<f:for each="{feed.data}" as="image" iteration="iteration">
			<f:if condition="{iteration.cycle} <= {settings.limit}">
				<div class="c-socialwall__item c-socialwall__item--instagram">
					<f:link.external uri="{image.permalink}" title="Instagram profile {settings.username}" target="_blank" rel="noopener">
						<instagram:isLocalImageExisting id="{image.id}">
							<f:then>
								<picture>
									<source srcset="{f:uri.image(src:'/typo3temp/assets/tx_instagram/{image.id}.jpg', width:'500c', height:'500c', fileExtension: 'webp')}" type="image/webp">
									<source srcset="{f:uri.image(src:'/typo3temp/assets/tx_instagram/{image.id}.jpg', width:'500c', height:'500c', fileExtension: 'jpg')}" type="image/jpeg">

									<img
										src="{f:uri.image(src:'/typo3temp/assets/tx_instagram/{image.id}.jpg', width:'500c', height:'500c')}"
										title="{image.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
										alt="{image.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
										loading="lazy" />
								</picture>
							</f:then>
							<f:else>
								<f:comment>
									If image is not available on the local machine (for any reasons), load from instagram directly
								</f:comment>
								<img
									src="{image.media_url}"
									title="{image.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
									alt="{image.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
									width="500"
									height="500" />
							</f:else>
						</instagram:isLocalImageExisting>

						<p>{image.caption}</p>
					</f:link.external>
				</div>
			</f:if>
		</f:for>
	</div>
</div>

</html>
```

### Styling

If you want to have basic styling for the default layout present in the extension, you can include the
static template "Instagram" on your page.
