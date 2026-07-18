# AuthFlow - Modern Login & Registration System

A professional, production-quality authentication web application built with **HTML5, CSS3, Bootstrap 5, Vanilla JavaScript, and AJAX (Fetch API)**. Features a modern glassmorphism UI, responsive mobile-first design, and comprehensive form validation.

## Features

### Core Features
- **Bootstrap 5 Grid System** - Fully responsive layout using rows, columns, and breakpoints
- **Glassmorphism UI** - Modern frosted glass design with backdrop filters
- **Mobile-First Design** - Optimized for 320px+ screens to 4K displays
- **Dark/Light Mode** - Persistent theme toggle with local storage
- **Real-time Validation** - Instant form feedback with animated error messages
- **Password Strength Meter** - Visual indicator for password security levels
- **AJAX Simulation** - Dummy API calls using Fetch API and local storage

### Pages
| Page | Description |
|------|-------------|
| **Home** | Hero section with typing effect, animated stats counters, feature cards |
| **Login** | Email/password validation, remember me, forgot password modal |
| **Register** | Full form with 6 fields, password match, username/email availability check |

### User Interface
- Smooth page transitions and scroll animations (AOS fallback via IntersectionObserver)
- Button ripple effects and hover lift animations
- Toast notification system (success/error/warning/info)
- Floating animated cards on hero section
- Animated counter statistics section
- Typing text effect on hero headline
- Back to top button with smooth scroll

### Form Validation
- **Email** - Format validation with regex
- **Password** - Min length, strength meter (weak/fair/good/strong)
- **Confirm Password** - Real-time match checking
- **Phone** - Format validation
- **Username** - Length (3-20), character set, availability check
- **Required fields** - All fields validated on blur and submit

### AJAX (Fetch API)
- Username availability check (simulated + jsonplaceholder)
- Email existence check (simulated + jsonplaceholder)
- Login request simulation
- Registration request simulation
- Local storage user persistence

## Tech Stack

| Technology | Usage |
|------------|-------|
| **HTML5** | Semantic markup, accessibility attributes |
| **CSS3** | Custom properties, animations, gradients, glassmorphism |
| **Bootstrap 5.3** | Grid, navbar, cards, buttons, modal, forms, utilities |
| **Bootstrap Icons** | UI iconography |
| **Google Fonts** | Poppins font family |
| **Vanilla JS** | Modular scripts, DOM manipulation, ES6+ |
| **Fetch API** | AJAX requests to jsonplaceholder + local simulation |

## File Structure

```
project/
│
├── index.html              # Home page with hero, stats, features
├── login.html               # Login page with form validation
├── register.html            # Registration page with full validation
│
├── css/
│   ├── style.css            # Main stylesheet (variables, components, animations)
│   └── responsive.css       # Responsive breakpoints (Mobile First)
│
├── js/
│   ├── app.js               # Core: navbar, footer, toast, theme, counters, typing
│   ├── login.js              # Login logic: validation, remember me, forgot password
│   ├── register.js           # Registration logic: validation, password meter, modals
│   └── ajax.js               # AJAX service: fetch wrappers, local DB simulation
│
├── assets/
│   ├── images/               # Image assets directory
│   └── icons/                # Icon assets directory
│
└── README.md                 # Documentation
```

## Installation

### Option 1: Direct Download
1. Download the project folder
2. Open any `.html` file in your browser (Chrome, Firefox, Edge, Safari)

### Option 2: Local Server (Recommended)
Using Python:
```bash
cd project
python -m http.server 8000
```
Visit `http://localhost:8000`

Using Node.js:
```bash
cd project
npx serve .
```

### Option 3: VS Code Live Server
1. Install the "Live Server" extension
2. Right-click on `index.html` → "Open with Live Server"

## Usage

### Navigation
- **Home** - Landing page with hero, stats, and features
- **Login** - Sign in with email and password
- **Register** - Create a new account

### Login
1. Enter your email and password
2. Toggle password visibility with the eye icon
3. Check "Remember Me" to persist email
4. Click "Forgot Password?" to open reset modal
5. Click "Sign In" - loading spinner shows during AJAX request

### Registration
1. Fill in all 6 fields with valid data
2. Watch password strength update in real-time
3. Username availability checks automatically on input
4. Password confirmation validates in real-time
5. Agree to terms and submit
6. Success modal appears with link to login

### Demo Credentials
For quick testing:
- Email: `admin@example.com` (with password `password123`)
- Or create a new account via the registration page

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+
- Opera 45+
- Mobile Chrome/Safari

## Customization

### Colors
Edit CSS variables in `css/style.css`:
```css
:root {
  --primary: #6c5ce7;
  --secondary: #00cec9;
  --success: #00b894;
  /* ... */
}
```

### Theme
Toggle between light/dark mode using the theme button in the navbar. Preference is saved to `localStorage`.

## Acknowledgments

- [Bootstrap 5](https://getbootstrap.com/)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
- [Google Fonts](https://fonts.google.com/)
- [JSONPlaceholder](https://jsonplaceholder.typicode.com/)

---
SS
