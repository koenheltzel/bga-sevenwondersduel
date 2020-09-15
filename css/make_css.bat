cd css
type *.css > ../sevenwondersduel_source.css
cd..
call npx postcss sevenwondersduel_source.css --use autoprefixer --output sevenwondersduel.css --no-map
del sevenwondersduel_source.css