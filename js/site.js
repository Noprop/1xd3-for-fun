(function () {
  function getEmailError(email) {
    if (email === "") return "Email is required.";

    var atIndex = email.indexOf("@");
    if (atIndex < 1) {
      return "Email must include an @ symbol with characters before it (e.g. user@example.com).";
    }

    var domain = email.substring(atIndex + 1);
    if (domain === "") {
      return "Email must have a domain after the @ symbol (e.g. user@example.com).";
    }

    var dotIndex = domain.indexOf(".");
    if (dotIndex < 1) {
      return "The domain must have at least one character before the dot (e.g. user@example.com).";
    }

    if (dotIndex >= domain.length - 1) {
      return "The domain must have at least one character after the dot (e.g. user@example.com).";
    }

    return "";
  }

  function getFieldLabel(input) {
    var label = input.closest(".field");
    var text = label ? label.querySelector("span") : null;
    return text ? text.textContent.trim() : "This field";
  }

  function getFieldError(input) {
    var value = input.value.trim();

    if (input.name === "email") {
      return getEmailError(value);
    }

    if (value === "") {
      return getFieldLabel(input) + " is required.";
    }

    if (input.name === "password" && input.minLength > 0 && input.value.length < input.minLength) {
      return "Password must be at least " + input.minLength + " characters.";
    }

    return "";
  }

  function setFieldError(input, message) {
    var field = input.closest(".field");
    var error = field ? field.querySelector(".field-error") : null;

    if (error) {
      error.textContent = message;
    }

    if (message === "") {
      input.removeAttribute("aria-invalid");
    } else {
      input.setAttribute("aria-invalid", "true");
    }
  }

  function validateField(input) {
    var message = getFieldError(input);
    setFieldError(input, message);
    return message === "";
  }

  function setupFormValidation(form) {
    var inputs = form.querySelectorAll("input");

    inputs.forEach(function (input) {
      input.addEventListener("input", function () {
        if (input.value.trim() === "") {
          setFieldError(input, "");
          return;
        }

        validateField(input);
      });
    });

    form.addEventListener("submit", function (event) {
      var valid = true;

      inputs.forEach(function (input) {
        if (!validateField(input)) {
          valid = false;
        }
      });

      if (!valid) {
        event.preventDefault();
      }
    });
  }

  function setupAuthPanel(panel) {
    var tabs = Array.from(panel.querySelectorAll("[data-form-target]"));
    var cards = Array.from(panel.querySelectorAll("[data-auth-form]"));
    var targets = tabs.map(function (tab) {
      return tab.dataset.formTarget;
    });
    var activeTab = panel.querySelector(".auth-tab.is-active");
    var defaultTarget = activeTab ? activeTab.dataset.formTarget : targets[0];

    function isValidTarget(target) {
      return targets.indexOf(target) !== -1;
    }

    function getUrlTarget() {
      var target = new URL(window.location.href).searchParams.get("panel");
      return isValidTarget(target) ? target : null;
    }

    function updateUrl(target) {
      var url = new URL(window.location.href);

      if (url.searchParams.get("panel") === target) {
        return;
      }

      url.searchParams.set("panel", target);
      window.history.pushState({ panel: target }, "", url);
    }

    function setActiveForm(target) {
      if (!isValidTarget(target)) {
        return;
      }

      tabs.forEach(function (tab) {
        var isActive = tab.dataset.formTarget === target;
        tab.classList.toggle("is-active", isActive);
        tab.setAttribute("aria-selected", isActive ? "true" : "false");
        tab.setAttribute("tabindex", isActive ? "0" : "-1");
      });

      cards.forEach(function (card) {
        card.classList.toggle("is-active", card.dataset.authForm === target);
      });
    }

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        var target = tab.dataset.formTarget;
        setActiveForm(target);
        updateUrl(target);
      });
    });

    window.addEventListener("popstate", function () {
      setActiveForm(getUrlTarget() || defaultTarget);
    });

    var initialTarget = getUrlTarget() || defaultTarget;
    if (initialTarget) {
      setActiveForm(initialTarget);
    }
  }

  var authPanel = document.querySelector("[data-auth-panel]");
  if (authPanel) {
    setupAuthPanel(authPanel);
  }

  document.querySelectorAll("[data-validate-form]").forEach(setupFormValidation);
})();
