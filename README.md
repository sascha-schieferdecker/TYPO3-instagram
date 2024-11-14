# Show a profile Instagram feed in TYPO3

### Based On https://github.com/in2code-de/instagram

Thanks a ton to the original author Alex Kellner!

## Introduction

As Meta will shutdown the Basic Display API, alternative ways to display instagram posts on your website are needed.

The extension allows you to display the latest posts and reels from your instagram profile by using https://apify.com 
as an API provider.

## Installation

Clone the repo into your local packages directory and set up your root composer.json as follows:

```json
...
  "repositories": [
    {
      "type": "path",
      "url": "packages/*",
      "options": {
        "symlink": true
      }
    },
...
```
Now you are able to install the extension from your local directory by using the "@dev" pattern:

`composer require saschaschieferdecker/instagram:@dev


## Configuration

### The instagram part

Sign up to apify.com and create a scrapers and set it up for the profiles to scrape:

apify/instagram-scraper

Make sure you don't select any filter (posts/reels) while setting up the scraper. Schedule the scraper to run as often as you like.

### The TYPO3 part

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

#### Cleanup Feeds

Delete all posts and media except the last 10 per feed:

```bash
./vendor/bin/typo3 instagram:cleanupfeed 10
 ```

### Scheduler

#### Import Feed

Add a new scheduler task of type `Execute console commands (scheduler)` and select `instagram:importfeed`. Now you can
add a frequency (e.g. `*/30 * * * *` for every 30 minutes), a instagram username and one (or more) email address if
error happens (and you want get notified).

| Field    | Description                                                                                                                             |
|----------|-----------------------------------------------------------------------------------------------------------------------------------------|
| notify   | Optional: Get notified via email if a CURL error occurs (e.g. if instagram blocks your requests). Commaseparated email list is provided. |
| post-url   | Apify Endpoint for Posts                                                                                                                |

### Cleanup Feeds

Add a new scheduler task of type `Execute console commands (scheduler)` and select `instagram:cleanupfeed`. Now you can add a frequency (e.g. `*/30 * * * *` for every 30 minutes).

| Field | Description                                                         |
|-------|---------------------------------------------------------------------|
| keep  | Number of posts to keep per feed. Unused images are deleted as well |

### Output

You can use the `tx_instagram_pi1` plugin to render the output of the extension in a page. There also is a plugin `tx_instagram_json` for JSON output, rather hacky, but working.

The difference in the JSON output vs. calling apify directly is that the plugin uses the locally stored displayUrl and videoUrl if possible.

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
	  xmlns:instagram="http://typo3.org/ns/SaschaSchieferdecker/Instagram/ViewHelpers"
	  data-namespace-typo3-fluid="true">

<div class="c-socialwall">
	<div class="c-socialwall">
		<f:for each="{feedposts}" as="post" iteration="iteration">
			<f:if condition="{iteration.cycle} <= {settings.limit}">
				<div class="c-socialwall__item c-socialwall__item--instagram">
					<f:link.external uri="{post.url}" title="Instagram profile {settings.username}" target="_blank"
									 rel="noopener">
						<f:if condition="{post.videoUrl}">
							<f:then>
								<instagram:isLocalImageExisting id="{post.id}">
									<f:then>
										<video src="/typo3temp/assets/tx_instagram/{post.id}.mp4"
											   width="500" height="500" preload="none"
											   style="height: 500px"
											   autoplay muted>
										</video>
									</f:then>
									<f:else>
										<video src="{post.videoUrl}"
											   width="500" height="500" preload="none"
											   style="height: 500px"
											   autoplay muted>
										</video>
									</f:else>
								</instagram:isLocalImageExisting>
							</f:then>
							<f:else>
								<instagram:isLocalImageExisting id="{post.id}">
									<f:then>
										<picture>
											<source
												srcset="{f:uri.image(src:'/typo3temp/assets/tx_instagram/{post.id}.jpg', width:'500c', height:'500c', fileExtension: 'jpg')}"
												type="image/jpeg">
											<img
												src="{f:uri.image(src:'/typo3temp/assets/tx_instagram/{post.id}.jpg', width:'500c', height:'500c')}"
												title="{post.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
												alt="{post.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
												loading="lazy"/>
										</picture>
									</f:then>
									<f:else>
										<f:comment>
											If image is not available on the local machine (for any reasons), load from
											instagram directly
										</f:comment>
										<img
											src="{post.displayUrl}"
											title="{post.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
											alt="{post.caption -> f:format.crop(maxCharacters: 120, append: ' ...')}"
											width="500"
											height="500"/>
									</f:else>
								</instagram:isLocalImageExisting>
							</f:else>
						</f:if>
						<p>{post.caption}</p>
					</f:link.external>
				</div>
			</f:if>
		</f:for>
	</div>
</div>

</html>


</html>
```

### Styling

If you want to have basic styling for the default layout present in the extension, you can include the
static template "Instagram" on your page.


### Example structure of a post

```json
{
  "inputUrl": "https://www.instagram.com/inotec_sicherheitstechnik/",
  "id": "3490949910395048751",
  "type": "Image",
  "shortCode": "DByWHKZx1Mv",
  "caption": "🕯️ Dichter Rauch steigt auf und das Unbekannte lauert am Ende des düsteren Flures … Aber keine Sorge, unsere Sicherheitsbeleuchtung erhellt den Weg und sorgt dafür, dass ihr sicher entkommen könnt! 👻\nUnser Team wünscht euch einen schaurig schönen Abend 🎃 𝗛𝗔𝗣𝗣𝗬 𝗛𝗔𝗟𝗟𝗢𝗪𝗘𝗘𝗡 🎃\n\n#inotec #wirsindinotec #mehralsnurlicht #sicherheit #sicherheitsbeleuchtung #notlicht #halloween #spooky #happyhalloween #pumpkin #kürbis #sicherheitsbeleuchtung #ense #soest #fluchtweg #brandschutz #sicherheitstechnik #notbeleuchtung #Darkness",
  "hashtags": [
    "inotec",
    "wirsindinotec",
    "mehralsnurlicht",
    "sicherheit",
    "sicherheitsbeleuchtung",
    "notlicht",
    "halloween",
    "spooky",
    "happyhalloween",
    "pumpkin",
    "kürbis",
    "ense",
    "soest",
    "fluchtweg",
    "brandschutz",
    "sicherheitstechnik",
    "notbeleuchtung",
    "Darkness"
  ],
  "mentions": [],
  "url": "https://www.instagram.com/p/DByWHKZx1Mv/",
  "commentsCount": 0,
  "firstComment": "",
  "latestComments": [],
  "dimensionsHeight": 1080,
  "dimensionsWidth": 1080,
  "displayUrl": "https://instagram.fmct5-1.fna.fbcdn.net/v/t39.30808-6/464808275_479417115110149_8104602989304730461_n.jpg?stp=dst-jpg_e15_fr_s1080x1080&_nc_ht=instagram.fmct5-1.fna.fbcdn.net&_nc_cat=101&_nc_ohc=ktULFj460owQ7kNvgFNs4aK&_nc_gid=e69443241ba4476b9cbeb8ca51dc0fe9&edm=APs17CUAAAAA&ccb=7-5&oh=00_AYA2RS4aMLnk48g2NqauSXcwQ32EVteJr9capit4XKsZcA&oe=673B948D&_nc_sid=10d13b",
  "images": [],
  "alt": "Photo by INOTEC Sicherheitstechnik GmbH in INOTEC Sicherheitstechnik GmbH. قد تكون صورة ‏رواق‏.",
  "likesCount": 37,
  "timestamp": "2024-10-31T11:21:20.000Z",
  "childPosts": [],
  "locationName": "INOTEC Sicherheitstechnik GmbH",
  "locationId": "2144055258953317",
  "ownerFullName": "INOTEC Sicherheitstechnik GmbH",
  "ownerUsername": "inotec_sicherheitstechnik",
  "ownerId": "58283711296",
  "isSponsored": false
}
```
License: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
