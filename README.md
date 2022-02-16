# Hitchhiker InnerTube Frontend

A frontend for the InnerTube/YouTubeI API, designed after YouTube's 2013 Hitchhiker. Written in PHP 7. **Heavily work in progress, I do not recommend use at the moment.**

## A disclaimer

I am a person with my own personal life. As such, updates may be rare. This is just a passion project, and I suggest you follow the development of Nightlinbit and aubymori's similar project on [/r/OldYouTubeLayout](https://old.reddit.com/r/oldyoutubelayout).

If you want to make pull requests of your own, please go ahead. I am not the best at PHP and that will show in the quality of my code.

## Current bugs I'm aware of

The following issues are already acknowledged, and probably will be patched in a later release.

- No player (I need to research player init, if you can contact Reprety/V3 Developer, please send him [/u/YukisCoffee](https://old.reddit.com/user/YukisCoffee). Thank you <3)
- Video and subscription counts display as 0 on watch. These simply are hardcoded placeholder values until the proper code is written.
- Missing category in video descriptions (needs player API, which currently isn't requested).
- Watch doesn't acknowledge being signed in.
- Logged out guide may sometimes display a duplicate channel.
- Expanded masthead playlists bar doesn't work (I got sent the resources necessary, so implementation should come eventually).
- Subscriptions cannot be sorted (new API issue, will look into workarounds).
- Home may occasionally display a null video. Seems like stricter checks need to be implemented.
- All the missing pages (oops lol)
- Just about everything else.

## Installation

**Currently, this has only been tested on my personal Windows 10 PC running PHP 7.4. You may encounter issues if you're using a different OS or are just not me. Please report these to me using the issues tab so that I may fix them (please specify OS/version, PHP version/configuration, and software used).**

You need PHP 7+ and a webserver. I recommend [XAMPP](https://www.apachefriends.org/download.html). Composer packages are currently bundled with the project, however I suggest you [install that](https://getcomposer.org/download/) as well.

Finally, getting it onto `www.youtube.com`: it may be possible to get it working with OS-native hosts file, however at the moment, you need external software that proxies it back to the domain:
- [Fiddler Classic (**recommended**)](https://www.telerik.com/download/fiddler/fiddler4)
- [Charles (download trial)](https://www.charlesproxy.com/download/)
- [mitmproxy](https://mitmproxy.org/)

### Fiddler Classic Configuration

<sub><sub>Don't use Fiddler Everywhere. It sucks.</sub></sub>

Enable decryption of HTTPS traffic (from browsers only), then set up an AutoResponder rule that redirects `REGEX:https://www.youtube.com/(.*)` to `http://127.0.0.1/$1` and tick the box that allows unmatched request passthrough. If you then want to hide Fiddler from view, then press `CTRL`+`M`. Fiddler will be accessible in the system tray.

I recommend using Fiddler Classic if you're on Windows, because it uses less RAM than Charles and it's more user-friendly than mitmproxy.

### Charles Configuration

Enable SSL proxying and install the root certificate in your browser, then go to "Map Remote" and add a rule to redirect host `www.youtube.com` to `http://127.0.0.1`. If you then want to hide Charles from view, press `CTRL`+`,` to open "Preferences", then click "Minimise to system tray".

### mitmproxy Configuration

You really got yourself into a mess, huh?
```py
import mitmproxy.http
        
class HitchhikerFrontendRedirect:
    def __init__(self):
        print("Hitchhiker Frontend Redirect active")
    
    def request(self, flow: mitmproxy.http.HTTPFlow):
        if "www.youtube.com" in flow.request.pretty_host:
            flow.request.host = "127.0.0.1"
            flow.request.port = 80
            flow.request.scheme = "http"
    
addons = [ HitchhikerFrontendRedirect() ]
```

But wait! There's more. mitmproxy doesn't allow this on HTTP 2 hosts, meaning you need to pass the `--no-http2` argument. Additionally, it likes to break WebSocket traffic, which was a common complaint levied against it in the GoodTube Discord server when that was around (it broke Discord image uploads), so you need to pass a TCP passthrough regex that ignores WS traffic. As such, I concocted this launch command just for you mitmproxy users:

```sh
mitmdump -s YOUR_PYTHON_FILE.py --no-http2 --tcp !(www.youtube.com)
```

Note that on Windows, the command is `mitmdump`. On Unix systems, this will be `mitmproxy` instead. Otherwise, the launch command should be the same.

I tested this with Discord image uploading, and it seems to work as it should. Please make an issue if problems arise.

### Hostsfile Configuration

Too badass for mitmproxy? Give hostsfile a try!

I did all the work for you, actually. The requests library overcomes the need for a proxy at all by using manual nameserver lookups that bypass your system's hostsfile for requests. **This may also be useful for you if you are having trouble with any proxy.**

All you need to do is host this on localhost, add the rule to your hostsfile, enable it in the frontend `config.json` file, and then... [set up SSL for it to work at all](https://www.webdesignvista.com/install-ssl-certificate-for-localhost-xampp-windows/). As it turns out, YouTube uses HSTS, so you will probably get an SSL error that your browser doesn't let you bypass instead of the YouTube website.

<sub>I still recommend using a proxy, just because it's more user friendly 🥰</sub>

## Roadmap

At this point in development, most things are unfinished or only partially finished.

- [x] Homepage (placeholder)
- [x] Guide menu
- [x] Signed in support
- [x] Watch page (partial)
- [ ] Search page
- [ ] Channel page
- [ ] Playlist page
- [ ] Other feed pages
- [ ] Translations
- [ ] Clean-up

---

Love you! ❤ A nice shoutout to the GoodTube community on Discord for getting this party started! Thank you all 🥰
