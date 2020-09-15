cd css
type *.css > ../sevenwondersduel_source.css
cd..
npx postcss sevenwondersduel_source.css --use autoprefixer --output sevenwondersduel.css --no-map
del sevenwondersduel_source.css
pause