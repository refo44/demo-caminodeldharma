ADD A NEW CAMINO DEL DHARMA BLOG ARTICLE TO PRODUCTION WORDPRESS USING SSH +
WP-CLI.

This is a CONTENT OPERATION.

Use the already-mapped Camino del Dharma Content Operations model.

Do not redo the full migration/content-model research unless production schema
drift is detected.

======================================================================
PRODUCTION TARGET
======================================================================

Canonical site:

https://caminodeldharma.org/

Production WordPress root:

/home/u548735796/domains/caminodeldharma.org/public_html

Post type:

post

Canonical URL:

/blog/{slug}

Known researched versions:

WordPress 7.1.2
theme camino-del-dharma 0.6.3
plugin camino-del-dharma-core 0.7.13

Known approved observed .htaccess snapshot at research time:

c40e7e4440269d24920771e2ee92da32d894efda5a194f48395ae93f7f89013b

Use the current documented approved snapshot if it has legitimately changed
since this research.

======================================================================
ABSOLUTE RULES
======================================================================

DO NOT:

- change application code
- change Git
- commit
- push
- create PRs/tags
- deploy
- modify plugin/theme
- modify wp-config.php
- modify .htaccess
- run rewrite flush
- save Permalinks
- modify DNS
- modify Hostinger
- modify SMTP
- enable the contact form
- use migration/payload.json as an editor
- restore static/
- edit test fixtures
- invent article facts
- invent quotations
- invent sources
- invent authors

Create a draft first.

Publication is a separate gate controlled by PUBLISH_MODE.

======================================================================
CURRENT ARTICLE CONTRACT
======================================================================

Post type:

post

Public URL:

/blog/{slug}

Listing:

/blog

Editorial-author relation:

authors

Storage:

one post-meta value containing a real PHP array of published `blog_author` IDs
in byline order.

Current known editorial authors:

6 = Comunidad Camino del Dharma
7 = Zheng Gong

Do NOT treat `post_author` as the editorial byline.

Do NOT create a new WordPress user to represent an editorial author.

If the owner requests a NEW editorial author:

STOP.

Return:

NEW BLOG AUTHOR PROFILE REQUIRED

unless a complete author-profile creation workflow has been separately
authorized.

Current supported article meta:

authors
post_featured

seo_title
seo_description
seo_keywords
og_title
og_description
seo_related_url

share_whatsapp
share_x
share_threads

Featured image:

_thumbnail_id

Image ALT:

_wp_attachment_image_alt

No separate share-image field.

Featured image supplies OG image.

======================================================================
ARTICLE RENDERING RULES
======================================================================

The theme automatically renders:

- H1
- excerpt/deck
- «Por {author}»
- linked author name(s)
- author bio(s)
- reading time
- featured image
- share control

Do NOT put these into post_content:

- H1
- byline
- author bio
- publication-date header
- reading time
- duplicate featured image

Body headings begin at H2.

H3 is only under an H2.

Canonical body structure:

<!-- wp:paragraph -->
<p>[OPENING]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[NEXT]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">[SECTION]</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[PARAGRAPH]</p>
<!-- /wp:paragraph -->

<!-- optional list -->

<!-- wp:list -->
<ul class="wp-block-list">
<li>[ITEM]</li>
</ul>
<!-- /wp:list -->

<!-- optional subsection -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">[SUBSECTION]</h3>
<!-- /wp:heading -->

<!-- optional quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
<p>[OWNER-SUPPLIED QUOTE]</p>
</blockquote>
<!-- /wp:quote -->

Only include optional blocks when the owner supplied the corresponding content.

======================================================================
EDITORIAL RULES
======================================================================

Write in Spanish (Colombia).

Voice:

- sober
- welcoming
- clear
- contemplative without grandiosity

Structure:

orient
→ explain
→ invite when appropriate

Do not start with marketing hooks.

Do not use exclamation marks.

Do not promise:

- healing
- peace
- transformation
- fulfillment

Do not narrate the reader's inner state.

Visible content uses:

Buddhismo
buddhista

Search metadata may use:

budismo
budista

Preserve the institutional name exactly:

Comunidad Buddhista Camino del Dharma

Avoid marketing vocabulary documented by the project.

Do not pad the article to hit a word count.

Recommended length bands are guidance only.

Excerpt:

one or two sentences containing the main idea.

======================================================================
PHASE 1 — VERIFY CURRENT PRODUCTION
======================================================================

SSH using the existing project connection.

Run:

cd /home/u548735796/domains/caminodeldharma.org/public_html

Create a private directory for this run. Do not reuse a fixed path under /tmp.

CDD_TMP=$(mktemp -d /tmp/cdd-article-XXXXXX)

Verify:

pwd -P
wp option get home
wp option get siteurl
wp option get blog_public
wp eval 'echo wp_get_environment_type(), PHP_EOL;'
wp core version
wp theme list --status=active
wp plugin list --status=active
sha256sum .htaccess

Require canonical production.

Record BEFORE .htaccess hash.

If theme/plugin versions changed since the content model was researched:

inspect current author/meta implementation.

If keys/semantics changed:

STOP.

Return:

CONTENT MODEL DRIFT DETECTED — ARTICLE IMPORT REQUIRES MODEL REVIEW.

If .htaccess differs from the current approved documented snapshot without an
approved later review:

STOP.

Do not repair it.

======================================================================
PHASE 2 — VALIDATE OWNER INPUT
======================================================================

Read ARTICLE INPUT below.

Required:

- TITLE
- SLUG
- PIECE_TYPE
- EXCERPT
- BODY
- EDITORIAL_AUTHORS
- FEATURED_IMAGE
- IMAGE_ALT
- PUBLICATION
- COMMENTS

Valid known piece types:

reflexión
artículo educativo
nota de contexto

Piece type is editorial/share context.

Do not invent a different type.

Validate all factual names, quotes and URLs against owner input only.

======================================================================
PHASE 3 — VERIFY EDITORIAL AUTHOR
======================================================================

For every requested existing author:

verify the blog_author ID is still:

- present
- published

Current known:

6 Comunidad Camino del Dharma
7 Zheng Gong

Use the IDs requested by the owner and preserve the supplied order.

Do not substitute authors.

Do not infer the author from article subject.

Do not use `post_author` for the public byline.

If owner requests an unknown/new author:

STOP before article creation unless a separately authorized author-profile
workflow exists.

======================================================================
PHASE 4 — DUPLICATE / SLUG CHECK
======================================================================

Search published, draft, scheduled and trash posts for:

- exact/similar title
- requested slug

Do not silently accept a WordPress-generated `-2`.

If the desired slug is occupied:

STOP for owner decision.

Canonical eventual URL:

https://caminodeldharma.org/blog/{slug}

No trailing slash.

======================================================================
PHASE 5 — FEATURED IMAGE
======================================================================

Verify FEATURED_IMAGE exists locally.

Inspect:

- type
- dimensions
- filesize
- filename

Current article image expectation:

3:2

Recommended:

1600 × 1067

Practical minimum:

1200 × 800

Do not reject a legitimate supplied asset only because it differs slightly;
report material departures before publication.

Check WordPress Media Library for an existing matching asset using filename and
dimensions.

Reuse an existing correct attachment rather than duplicate it.

If new:

copy through SSH/SCP to a unique file in account `/tmp`.

Do NOT stage it in public_html.

Import through WP-CLI.

Capture ATTACHMENT_ID.

Set exact approved IMAGE_ALT.

Do not invent caption.

======================================================================
PHASE 6 — INLINE ASSETS
======================================================================

If INLINE_ASSETS are empty:

do nothing.

Current production articles normally do not require inline images.

For every supplied inline image:

- verify file
- import/reuse Media Library attachment
- set owner-approved ALT
- insert only at requested placement

Do not invent image placement.

Do not create a gallery.

======================================================================
PHASE 7 — PREPARE ARTICLE BODY
======================================================================

Convert the approved BODY to valid Gutenberg HTML.

Do not materially rewrite it.

Allowed mechanical operations:

- paragraph/block markup
- heading markup
- lists
- quote markup
- link markup
- HTML escaping
- obvious punctuation correction that does not change meaning

Do not add facts.

Do not add quotes.

Do not add sources.

Do not add a references section unless supplied.

Apply emphasis when the approved text has a name, a time, a role, or one
short fact the reader must not miss. Do not decorate a sentence that has
nothing to scan. Do not bold a whole paragraph.

Bold is only:

<strong style="font-weight:600">[TEXT]</strong>

The theme Inter face has no weight 700, so a bare <strong> does not look
bold.

Italic is <em>[TEXT]</em>. Use it for a role or a short qualifier, not for
the person's name.

Ensure:

- no H1
- no byline
- no author bio
- no reading-time text
- H2/H3 hierarchy is valid

External links:

- use meaningful link text
- no «aquí», «ver más», «clic»
- target="_blank"
- rel="noopener noreferrer"
- include the site's accessible indication that the link opens in a new tab

If an ARTICLE_CTA is explicitly supplied, use the established article CTA
structure.

Do not invent a CTA.

Write to a temporary file such as:

"$CDD_TMP/body.html"

======================================================================
PHASE 8 — PREPARE EXCERPT / SEO
======================================================================

EXCERPT is public deck/card copy.

Use exact approved excerpt.

SEO_TITLE:

If supplied:
store it.

If AUTO/empty:
leave custom field empty and use normal fallback.

META_DESCRIPTION:

If exact text is supplied:
store it.

If:

AUTO_FROM_EXCERPT

create the documented concise trim of the approved excerpt, approximately
155 characters maximum, without adding claims.

KEYWORDS:

Only owner-supplied.

Empty is valid.

OG_TITLE / OG_DESCRIPTION:

Only custom when supplied.

Otherwise allow fallback.

RELATED_URL:

Only owner-supplied absolute URL.

======================================================================
PHASE 9 — PREPARE SHARE TEXT
======================================================================

Supported:

share_whatsapp
share_x
share_threads

The text keeps literal:

{{SHARE_URL}}

If exact share messages are supplied:

store them.

If:

DRAFT_FOR_REVIEW

construct drafts using only:

- approved piece type
- approved editorial author(s)
- approved title
- Camino del Dharma

Do not publish the article if automatically drafted share copy still requires
owner approval.

Return those drafts with the article draft.

======================================================================
PHASE 10 — COMMENTS DECISION
======================================================================

This is intentionally explicit.

Existing articles are technically:

comment_status=open

but the current theme renders no comments UI.

The researched safe recommendation for new content is:

closed

Use the exact ARTICLE INPUT value:

COMMENTS: closed
or
COMMENTS: open

Do not silently copy old posts.

======================================================================
PHASE 11 — CREATE ARTICLE DRAFT
======================================================================

Create the native WordPress post as:

post_type = post
post_status = draft

Use:

- approved title
- approved slug
- approved excerpt
- prepared temporary body file
- chosen comment_status

SHELL ARGUMENTS

Do not place approved text inside single quotes. An apostrophe, such as O'Connor, breaks that command.

Write each approved string to its own file with a quoted heredoc. The closing line must not occur inside the text. Do not escape or rewrite the approved text.

cat > "$CDD_TMP/title.txt" <<'EOF'
APPROVED_TITLE
EOF

Pass the file as one argument. The quotes around the substitution keep spaces and apostrophes in the stored value. Do not insert a bare `--` before it. WP-CLI 2.12 treats that as another positional argument.

"$(cat "$CDD_TMP/title.txt")"

Create the draft with this command. If it exits non-zero, STOP.

wp post create "$CDD_TMP/body.html" --post_type=post --post_status=draft --post_title="$(cat "$CDD_TMP/title.txt")" --post_name="$(cat "$CDD_TMP/slug.txt")" --post_excerpt="$(cat "$CDD_TMP/excerpt.txt")" --comment_status=<COMMENTS> --ping_status=closed --porcelain

`<COMMENTS>` is the approved value, `closed` or `open`. `--ping_status=closed` is always set, including when comments stay open.

Use an existing authorized WordPress user/context.

Do not create a new WordPress user.

Do not use WordPress post_author as the editorial attribution.

Capture POST_ID.

======================================================================
PHASE 12 — SET EDITORIAL AUTHORS
======================================================================

This is critical.

The `authors` value MUST be a REAL PHP ARRAY of published blog_author IDs.

Do NOT do:

wp post meta update POST_ID authors '6'

Do NOT store:

"6"
"6,7"
"[6,7]"
JSON

`wp eval` does not receive trailing arguments in `$args`. Write a temporary script and run it with `wp eval-file`, which does.

Write `"$CDD_TMP/authors.php"` containing only:

<?php
if ( count( $args ) < 2 ) {
    WP_CLI::error( 'POST_ID and at least one author id are required' );
}
$author_ids = array_map( 'intval', array_slice( $args, 1 ) );
update_post_meta( (int) $args[0], 'authors', $author_ids );
$stored = get_post_meta( (int) $args[0], 'authors', true );
if ( $stored !== $author_ids ) {
    WP_CLI::error( 'authors was not stored' );
}

Then run:

wp eval-file "$CDD_TMP/authors.php" POST_ID AUTHOR_ID [AUTHOR_ID...]

If that command exits non-zero, STOP. Do not store authors with `wp post meta update`.

Preserve owner-specified order.

Read the value back and confirm it is an array.

======================================================================
PHASE 13 — SET FEATURED / SEO / SHARE
======================================================================

Set:

_thumbnail_id = ATTACHMENT_ID

post_featured:

default false/0.

Set true only if:

FEATURED_ON_HOME: yes

Apply supported SEO/share fields as approved.

Do not create categories or tags by default.

Current default:

Uncategorized
no tags

If the owner explicitly requested a new category/tag:

STOP unless that creation was explicitly authorized.

Do not invent taxonomy.

======================================================================
PHASE 14 — VALIDATE DRAFT
======================================================================

Read back:

wp post get POST_ID
wp post meta list POST_ID

Verify:

- draft
- title
- slug
- excerpt
- body
- authors PHP array
- order
- featured image
- ALT
- post_featured
- SEO
- share
- comment_status equals the approved COMMENTS value
- ping_status = closed
- publication intent

Verify the author profile URL(s):

/author/{slug}

Verify draft contains no:

- H1
- pasted byline
- bio
- reading-time text
- retired hostnames

======================================================================
PHASE 15 — PUBLICATION / SCHEDULING GATE
======================================================================

Read:

PUBLISH_MODE
PUBLICATION

Before interpreting PUBLICATION, read:

wp option get timezone_string

Require:

America/Bogota

If the value is anything else, including empty or a numeric offset:

STOP.

Do not change the site timezone during this operation.
Do not schedule or publish until this check passes.
`post_date` is parsed in the site timezone.

If:

PUBLISH_MODE: draft

leave as draft.

Do not schedule/publish.

If publication requires owner review of automatically prepared share/SEO copy:

leave as draft regardless of PUBLISH_MODE.

Report what still requires approval.

If:

PUBLISH_MODE: publish
PUBLICATION: now

and all content is approved:

publish.

If:

PUBLISH_MODE: publish
PUBLICATION: YYYY-MM-DD HH:MM:SS

interpret it in:

America/Bogota

Set editorial authors BEFORE changing to `future`.

Schedule using WordPress-supported:

post_status=future
post_date=<Colombia datetime>

Verify resulting:

post_status
post_date
post_date_gmt

Do not invent a publication datetime.

======================================================================
PHASE 16 — POST-PUBLISH VERIFICATION
======================================================================

For immediate publication verify:

https://caminodeldharma.org/blog/{slug}

returns 200.

Verify:

- canonical slashless URL
- `/blog` contains it
- H1
- excerpt/deck
- correct «Por ...»
- correct linked author(s)
- author bio(s)
- reading time
- featured image
- image ALT
- body structure
- external links
- SEO title
- meta description
- OG image
- BlogPosting JSON-LD
- sitemap eligibility

Verify each:

/author/{slug}

correctly lists the new article.

If `post_featured=true`:

verify home featured article behavior.

If false:

do not expect it in the featured column.

It may still appear in the normal latest-blog area.

Do not flush rewrites.

Do not purge LiteSpeed unless stale output is actually observed.

======================================================================
PHASE 17 — SCHEDULED ARTICLE VERIFICATION
======================================================================

If scheduled rather than immediately published:

do NOT expect public 200 yet.

Verify:

post_status = future
post_date = requested America/Bogota time
post_date_gmt = correct UTC conversion

Verify `authors` is populated.

Report scheduled canonical URL.

Do not wait for the future publication.

======================================================================
PHASE 18 — FINAL INFRASTRUCTURE CHECK
======================================================================

Verify:

home
siteurl
environment
blog_public

unchanged.

Verify .htaccess hash equals BEFORE hash.

If different:

DO NOT restore.

Report both hashes.

Verify no unrelated post was modified.

======================================================================
PHASE 19 — TEMP CLEANUP
======================================================================

Remove only this run's private directory:

rm -rf "$CDD_TMP"

Do not delete any other path under /tmp.

Keep:

- WordPress Media Library files
- owner source assets
- backups
- repo files

======================================================================
FINAL REPORT
======================================================================

Return:

# Blog Article Content Operation Report

## Production
- root
- environment
- home
- siteurl
- WordPress
- theme
- plugin
- .htaccess before
- .htaccess after

## Article
- ID
- status
- title
- slug
- expected/public URL
- publication datetime

## Editorial Model
- piece type
- author IDs/names in order
- PHP array validation
- WordPress post_author not used for public byline

## Content
- excerpt
- body structure
- H1 absent
- headings valid
- CTA if supplied
- links/accessibility

## Media
Table:
| Attachment | File | Role | Dimensions | ALT | Reused/New |

## Taxonomy
- category
- tags

## Featured
- post_featured
- expected home effect

## SEO / Share
- SEO title behavior
- meta description
- keywords
- OG
- related URL
- WhatsApp
- X
- Threads

## Comments
- requested setting
- stored setting

## Validation
- duplicate check
- author
- media
- accessibility
- editorial style
- metadata

## Public Verification
If published:
- single
- /blog
- author archive(s)
- home behavior
- sitemap
- SEO
- BlogPosting
- broken link check
- retired hostname check

If scheduled:
- future status
- local datetime
- GMT datetime

## Safety
Confirm:
- no code changes
- no Git changes
- no theme/plugin changes
- no .htaccess modification
- no rewrite flush
- no DNS changes
- no SMTP changes
- no migration commands
- only the intended article/media were created

If draft:

BLOG ARTICLE CREATED AND VALIDATED AS DRAFT — READY FOR OWNER REVIEW.

If scheduled:

BLOG ARTICLE SCHEDULED AND VALIDATED ON CANONICAL WORDPRESS PRODUCTION.

If published:

BLOG ARTICLE PUBLISHED AND VERIFIED ON CANONICAL WORDPRESS PRODUCTION.

======================================================================
ARTICLE INPUT
======================================================================

PUBLISH_MODE: draft

TITLE:
[REQUIRED]

SLUG:
[REQUIRED — OWNER CONFIRMED]

PIECE_TYPE:
[reflexión | artículo educativo | nota de contexto]

EXCERPT:
[REQUIRED — 1–2 approved sentences]

BODY:
[REQUIRED — paste complete approved article]

EDITORIAL_AUTHORS:
[REQUIRED — ordered list]
[6 Comunidad Camino del Dharma]
[7 Zheng Gong]

PUBLICATION:
[now | YYYY-MM-DD HH:MM:SS Colombia]

COMMENTS:
[closed | open]

FEATURED_IMAGE:
[REQUIRED — local file path]

IMAGE_ALT:
[REQUIRED]

INLINE_ASSETS:
[OPTIONAL — each file + exact placement + ALT]

ARTICLE_CTA:
[OPTIONAL — label + absolute owner URL]

FEATURED_ON_HOME:
[yes | no — default no]

SEO_TITLE:
[OPTIONAL | AUTO]

META_DESCRIPTION:
[exact text | AUTO_FROM_EXCERPT]

KEYWORDS:
[OPTIONAL]

OG_TITLE:
[OPTIONAL]

OG_DESCRIPTION:
[OPTIONAL]

RELATED_URL:
[OPTIONAL]

SHARE_WHATSAPP:
[exact text using {{SHARE_URL}} | DRAFT_FOR_REVIEW]

SHARE_X:
[exact text using {{SHARE_URL}} | DRAFT_FOR_REVIEW]

SHARE_THREADS:
[exact text using {{SHARE_URL}} | DRAFT_FOR_REVIEW]

SOURCES:
[OPTIONAL — real sources cited by the article only]

OTHER_OWNER_NOTES:
[OPTIONAL]
