# Mobius Smart Recycling Bin - Bin Client

The React 19 application that runs on the screen mounted at the physical
recycling bin. Captures the disposal image, sends it to the Laravel server for
classification and points calculation, and displays the result to the user.

This is a standalone Vite project. It does not share a build with the Laravel
backend; it is deployed independently to the bin-mounted screen and reaches the
backend over HTTP on the same Wi-Fi network.

## Stack

- React 19
- TypeScript
- Vite 8
- Tailwind CSS

## First-time setup

```sh
npm install
cp .env.example .env
# then edit .env and set VITE_API_URL to the Laravel server's local IP and port
```

`VITE_API_URL` should point at the Laravel API root. When the Laravel server is
started with `php artisan serve --host 0.0.0.0 --port 8000`, the value is the
host machine's local Wi-Fi IP, for example:

```
VITE_API_URL=http://192.168.1.50:8000/api/v1
```

## Running

```sh
# development with hot module replacement
npm run dev

# production build, output in ./dist
npm run build

# preview the built output locally
npm run preview
```

## Deployment to the bin screen

1. Run `npm run build` on the development machine.
2. Copy the `dist/` folder to the bin screen, or point a static web server at it.
3. Open the index page in fullscreen kiosk mode in the browser on the bin
   screen.
4. Confirm that the bin screen and the Laravel server share the same Wi-Fi
   network and that the bin screen can reach `VITE_API_URL` from its browser.

## Notes

- The bin client is light by design. All business logic, including the points
  calculation rule and the two-stage detection pipeline, lives on the Laravel
  server. The bin client only captures, posts, and displays.
