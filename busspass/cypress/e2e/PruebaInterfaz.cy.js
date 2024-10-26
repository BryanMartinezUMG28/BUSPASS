describe('Prueba de Buscar Viaje', () => {
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

    // Iniciar sesión
    cy.get('input[name="email"]').type('salomevasquez100@gmail.com');
    cy.get('input[name="password"]').type('root123');
    cy.get('button[type="submit"]').click();

    // Verificar que se haya redirigido al dashboard correctamente
    cy.url({ timeout: 10000 }).should('include', '/bus');

    // Verificar que el elemento "Dashboard" esté visible en la pantalla
    cy.contains('Dashboard', { timeout: 20000 });
  });

  it('Debería poder consultar los viajes que existen dependiendo fecha, origin y destino', () => {
    // Paso 1: Consultar viaje
    cy.visit('http://localhost:8000/bus/viajes/consultar');

    // Verificar que el formulario de consulta esté presente en la vista de "Consultar Viajes"
    cy.get('form').should('be.visible');

    // Seleccionar el origen, destino y fecha del viaje
    cy.get('select[name="origen"]', { timeout: 10000 }).should('be.visible').select('1');
    cy.get('select[name="destino"]', { timeout: 10000 }).should('be.visible').select('2');
    cy.get('input[name="fecha"]', { timeout: 10000 }).should('be.visible').type('2024-12-12');

    // Enviar el formulario para consultar los viajes
    cy.get('form#form-consulta-viajes button[type="submit"]').should('be.visible').click();

    // Verificar que la URL cambió a la página de resultados
    cy.url({ timeout: 10000 }).should('include', '/bus/viajes/resultado');

  });
});
