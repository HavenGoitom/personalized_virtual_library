document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('[data-toggle-target]').forEach(function (button) {
    button.addEventListener('click', function () {
      var target = document.querySelector(button.getAttribute('data-toggle-target'));
      if (!target) return;
      target.classList.toggle('hidden');
    });
  });

  // Live validation: required, email, url, password confirmation
  function showError(input, message) {
    let err = input.parentNode.querySelector('.input-error');
    if (!err) {
      err = document.createElement('div');
      err.className = 'input-error';
      input.parentNode.appendChild(err);
    }
    err.textContent = message;
    input.classList.add('invalid');
  }

  function clearError(input) {
    let err = input.parentNode.querySelector('.input-error');
    if (err) err.textContent = '';
    input.classList.remove('invalid');
  }

  function validateInput(input) {
    const val = input.value.trim();
    if (input.hasAttribute('required') && val === '') {
      showError(input, 'This field is required');
      return false;
    }
    if (input.type === 'email' && val !== '' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) {
      showError(input, 'Please enter a valid email');
      return false;
    }
    if (input.type === 'url' && val !== '' && !/^https?:\/\/.+/.test(val)) {
      showError(input, 'Please enter a valid URL (https://...)');
      return false;
    }
    if ((input.name === 'password' || input.type === 'password') && input.name !== 'password_confirm') {
      if (val !== '' && val.length < 8) {
        showError(input, 'Password must be at least 8 characters');
        return false;
      }
    }
    // password confirmation
    if (input.name === 'password_confirm') {
      const form = input.form;
      if (form) {
        const pw = form.querySelector('input[name="password"]');
        if (pw && pw.value !== input.value) {
          showError(input, 'Passwords do not match');
          return false;
        }
      }
    }

    clearError(input);
    return true;
  }

  document.querySelectorAll('form').forEach(function (form) {
    form.querySelectorAll('input,textarea,select').forEach(function (input) {
      // attach live validation
      input.addEventListener('input', function () {
        validateInput(input);
      });
      // validate on blur as well
      input.addEventListener('blur', function () {
        validateInput(input);
      });
    });

    // prevent submit if client-side invalid
    form.addEventListener('submit', function (e) {
      let valid = true;
      form.querySelectorAll('input,textarea,select').forEach(function (input) {
        if (!validateInput(input)) valid = false;
      });
      if (!valid) {
        e.preventDefault();
      }
    });
  });

  function hasStrongPassword(value) {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/.test(value);
  }

  function attachConfirmForms(root) {
    const scope = root || document;
    scope.querySelectorAll('form[data-confirm]').forEach(function (form) {
      if (form._confirmAttached) return;
      form._confirmAttached = true;
      form.addEventListener('submit', function (event) {
        if (!confirm(form.getAttribute('data-confirm'))) {
          event.preventDefault();
        }
      });
    });
  }

  attachConfirmForms(document);

  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    let searchTimeout = null;
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function () {
        const query = searchInput.value.trim();
        const endpoint = window.location.pathname.replace(/\/books(\/.*)?$/, '/books');
        fetch(endpoint + '?search=' + encodeURIComponent(query), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (response) { return response.text(); })
          .then(function (html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const replacement = doc.querySelector('#book-results');
            if (replacement) {
              const target = document.getElementById('book-results');
              target.innerHTML = replacement.innerHTML;
              attachConfirmForms(target);
              window.history.replaceState(null, '', endpoint + (query ? '?search=' + encodeURIComponent(query) : ''));
            }
          });
      }, 250);
    });
  }

  document.querySelectorAll('input[name="password"]').forEach(function (passwordInput) {
    passwordInput.addEventListener('input', function () {
      const form = passwordInput.form;
      const pw = passwordInput.value.trim();
      if (pw !== '' && !hasStrongPassword(pw)) {
        showError(passwordInput, 'Password needs 8+ chars, a lowercase, uppercase, a digit, and a special character');
      } else {
        validateInput(passwordInput);
      }
    });
  });
});
