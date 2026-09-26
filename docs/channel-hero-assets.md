# Channel hero assets

The four channel landing pages serve static WebP heroes from
`public/images/hero/channels/`. These preserve the existing photos; browsers no
longer contact Unsplash to load these heroes.

Original sources:

- Programs: https://images.unsplash.com/photo-1517245386807-bb43f82c33c4
- Opportunities: https://images.unsplash.com/photo-1517048676732-d65bc937f952
- Multimedia: https://images.unsplash.com/photo-1551818255-e6e10975bc17
- Publications: https://images.unsplash.com/photo-1450101499163-c8848c66ca85

Sources were downloaded as JPEG with `fm=jpg&fit=crop&w=1600&q=85`, then
encoded using `cwebp -q 76 -resize WIDTH 0 SOURCE -o DESTINATION` at widths
640, 960, and 1600. Keep all three variants when replacing a photo.

`ChannelHero` supplies the static srcset. The shared hero emits the same srcset
and `100vw` sizes on the image and its head preload so the browser can reuse
the selected request. Only this hero is preloaded on each channel landing page.
Other images use their existing loading rules, with opportunity and publication
featured images loading lazily below the hero.

Deploy the image files together with the templates and PHP helper. No runtime
image conversion or remote image request is needed for these heroes. Actual LCP
improvement should be measured on production with representative mobile data;
this change does not assert a measured performance score.
