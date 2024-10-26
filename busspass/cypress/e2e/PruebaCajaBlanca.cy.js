describe('Prueba de Alerts de Campos Vacíos en Login', () => {
  beforeEach(() => {
    // Ignorar errores no capturados que podrían originarse en la aplicación
    cy.on('uncaught:exception', (err, runnable) => {
      if (err.message.includes("Cannot read properties of null (reading 'getAttribute')")) {
        return false;
      }
      return true;
    });

    // Visitar la página de inicio de sesión antes de cada prueba
    cy.visit('http://localhost:8000/bus/login');
  });

  describe('Validaciones de Inicio de Sesión', () => {
    it('Debería mostrar mensajes de error cuando no se ingresan el correo ni la contraseña', () => {
      // Dejar los campos vacíos y enviar el formulario
      cy.get('button[type="submit"]').click();

      //checkValidity para forzar la validación de los campos y observar los mensajes nativos
      cy.get('input[name="email"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity(); //alerta nativa del navegador
        }
        expect(isValid).to.be.false;
      });

      cy.get('input[name="password"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity(); //alerta nativa del navegador
        }
        expect(isValid).to.be.false;
      });

      cy.wait(3000);
    });

    it('Debería mostrar mensaje de error cuando solo se ingresa el correo pero no la contraseña', () => {
      // Ingresar solo el correo
      cy.get('input[name="email"]').type('salomevasquez100@gmail.com');
      cy.get('button[type="submit"]').click();

      // Verificar que el campo de contraseña sea inválido
      cy.get('input[name="password"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity();
        }
        expect(isValid).to.be.false;
      });

      cy.wait(3000);
    });

    it('Debería mostrar mensaje de error cuando solo se ingresa el contraseña pero no el correo', () => {
      // Ingresar solo la contraseña
      cy.get('input[name="password"]').type('root123');
      cy.get('button[type="submit"]').click();

      // Verificar que el campo de correo sea inválido
      cy.get('input[name="email"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity();
        }
        expect(isValid).to.be.false;
      });

      cy.wait(3000);
    });
  });

  describe('Prueba de Alerts de Campos Vacíos en Búsqueda de Viajes', () => {
    beforeEach(() => {
      // Iniciar sesión primero
      cy.get('input[name="email"]').type('salomevasquez100@gmail.com');
      cy.get('input[name="password"]').type('root123');
      cy.get('button[type="submit"]').click();

      // Verificar que se haya redirigido al dashboard correctamente
      cy.url({ timeout: 10000 }).should('include', '/bus');
      cy.contains('Dashboard', { timeout: 20000 });
    });

    it('Debería mostrar mensajes de error cuando los campos de consulta están vacíos', () => {
      // Visitar la página de consulta de viajes
      cy.visit('http://localhost:8000/bus/viajes/consultar');
  
      // Dejar todos los campos vacíos y enviar el formulario
      cy.get('form#form-consulta-viajes button[type="submit"]').click();
  
      // Usar checkValidity para forzar la validación de los campos
      cy.get('input[name="origen"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity();
        }
        expect(isValid).to.be.false;
      });

      cy.get('input[name="destino"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity();
        }
        expect(isValid).to.be.false;
      });

      cy.get('input[name="fecha"]').then(($el) => {
        const isValid = $el[0].checkValidity();
        if (!isValid) {
          $el[0].reportValidity();
        }
        expect(isValid).to.be.false;
      });
      
      cy.wait(3000);
    });
  });
});
