I believe the purpose of this plugin is to provide **simple** creation of maps on their WordPress sites, using a **basic** Leaflet JS setup.  I **will not accept** pull requests that incorporate any other [Leaflet plugin](http://leafletjs.com/plugins.html) into this one, or any copies of them, or any links to them (unless they are completely simplistic and globally usable).  Obviously I don't want to load dependencies for every user of this plugin that only a handful of users want.  

Also, please keep your pull requests limited to one feature/improvement each, as a courtesy to me who has to look through it trying to figure out what does what (and if it works at all).  Any number of bug fixes is completely acceptable. :)

For more expectations, please view the project's [Code of Conduct](https://github.com/bozdoz/wp-plugin-leaflet-map/blob/master/CODE_OF_CONDUCT.md)

### New Versions

New versions need to be tagged via `git tag`, following a process like this:

1. Check what changed since last tag:

```sh
npm run changes
```

2. Update the old tag everywhere:

- readme.txt (update the changelog with output from `npm run changes`)
- package.json
- package-lock.json
- leaflet-map.php (2 places)

3. Commit:

```sh
git commit -am 'v1.2.3: summary of update'
```

4. Tag:

```sh
git tag v.1.2.3
```

5. Push:

```sh
git push
git push --tags
```
