const API = {
  BASE: 'https://jsonplaceholder.typicode.com',
  USERS: 'https://jsonplaceholder.typicode.com/users',
  POSTS: 'https://jsonplaceholder.typicode.com/posts'
};



const LocalDB = {
  _users: JSON.parse(localStorage.getItem('authflow_users') || '[]'),

  save() {
    localStorage.setItem('authflow_users', JSON.stringify(this._users));
  },

  findByUsername(username) {
    return this._users.find(u => u.username.toLowerCase() === username.toLowerCase());
  },

  findByEmail(email) {
    return this._users.find(u => u.email.toLowerCase() === email.toLowerCase());
  },

  addUser(user) {
    this._users.push({ ...user, id: Date.now(), createdAt: new Date().toISOString() });
    this.save();
    return user;
  },

  validateLogin(email, password) {
    return this._users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.password === password);
  }
};

function checkUsernameAvailable(username) {
  return new Promise((resolve) => {
    const taken = [
      'admin', 'user', 'test', 'john', 'jane', 'root', 'superuser',
      'demo', 'guest', 'admin123', 'webmaster', 'support'
    ];

    setTimeout(() => {
      const localUser = LocalDB.findByUsername(username);
      const isTaken = taken.includes(username.toLowerCase()) || !!localUser;
      resolve({ available: !isTaken, message: isTaken ? 'Username is already taken' : 'Username is available' });
    }, 800);
  });
}

 
function checkEmailExists(email) {
  return new Promise((resolve) => {
    const existingEmails = [
      'admin@example.com', 'test@example.com', 'user@example.com',
      'john@example.com', 'demo@example.com'
    ];

    setTimeout(() => {
      const localUser = LocalDB.findByEmail(email);
      const exists = existingEmails.includes(email.toLowerCase()) || !!localUser;
      resolve({ exists, message: exists ? 'Email is already registered' : 'Email is available' });
    }, 800);
  });
}


function simulateLogin(email, password) {
  return new Promise((resolve, reject) => {
    showToast('Authenticating...', 'info');

    fetch(`${API.USERS}?email=${encodeURIComponent(email)}`)
      .then(response => {
        if (!response.ok) throw new Error('Network error');
        return response.json();
      })
      .then(users => {
        setTimeout(() => {
          const localUser = LocalDB.validateLogin(email, password);
          const jsonUser = users.find(u => u.email.toLowerCase() === email.toLowerCase());

          if (localUser) {
            resolve({
              success: true,
              user: { name: localUser.fullName || localUser.username, email: localUser.email },
              token: 'mock-jwt-token-' + Date.now(),
              message: 'Login successful!'
            });
          } else if (jsonUser && password === 'password123') {
            resolve({
              success: true,
              user: { name: jsonUser.name, email: jsonUser.email },
              token: 'mock-jwt-token-' + Date.now(),
              message: 'Login successful!'
            });
          } else {
            if (users.length === 0 && !localUser) {
              resolve({
                success: false,
                message: 'No account found with this email. Please register first.'
              });
            } else {
              resolve({
                success: false,
                message: 'Invalid password. Please try again.'
              });
            }
          }
        }, 1200);
      })
      .catch(() => {
        setTimeout(() => {
          const result = simulateLoginLocal(email, password);
          resolve(result);
        }, 1000);
      });
  });
}

 
function simulateLoginLocal(email, password) {
  const localUser = LocalDB.validateLogin(email, password);

  if (localUser) {
    return {
      success: true,
      user: { name: localUser.fullName || localUser.username, email: localUser.email },
      token: 'mock-jwt-token-' + Date.now(),
      message: 'Login successful! Welcome back.'
    };
  }

  const exists = LocalDB.findByEmail(email);
  if (!exists) {
    return {
      success: false,
      message: 'No account found with this email. Please register first.'
    };
  }

  return {
    success: false,
    message: 'Invalid password. Please try again.'
  };
}

 
function simulateRegistration(userData) {
  return new Promise((resolve) => {
    showToast('Creating your account...', 'info');

    setTimeout(() => {
      const existingEmail = LocalDB.findByEmail(userData.email);
      const existingUsername = LocalDB.findByUsername(userData.username);

      if (existingEmail) {
        resolve({ success: false, message: 'An account with this email already exists.' });
        return;
      }

      if (existingUsername) {
        resolve({ success: false, message: 'This username is already taken.' });
        return;
      }

      LocalDB.addUser(userData);

      resolve({
        success: true,
        message: 'Account created successfully! Welcome to AuthFlow.',
        user: { name: userData.fullName, email: userData.email }
      });
    }, 1500);
  });
}

 
function fetchUsers() {
  return fetch(API.USERS)
    .then(res => {
      if (!res.ok) throw new Error('Failed to fetch users');
      return res.json();
    })
    .then(users => users.slice(0, 5));
}
 
async function apiRequest(url, options = {}) {
  try {
    const response = await fetch(url, {
      headers: { 'Content-Type': 'application/json', ...options.headers },
      ...options
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    return await response.json();
  } catch (error) {
    showToast(error.message, 'error');
    throw error;
  }
}
