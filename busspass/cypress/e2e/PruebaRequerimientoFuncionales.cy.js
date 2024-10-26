describe('Prueba de Inicio de Sesión', () => {
    beforeEach(() => {
        // Visitar la página de inicio de sesión antes de cada prueba
        cy.visit('http://localhost:8000/bus/login');
    });

    it('Debería iniciar sesión con credenciales válidas (simulado)', () => {
        // Interceptar la solicitud de inicio de sesión
        cy.intercept('POST', '/bus/login', {
            statusCode: 200,
            body: {
                success: true,
                message: 'Inicio de sesión exitoso. Bienvenido.',
                redirect: '/bus'
            }
        }).as('loginRequest');

        // Usar credenciales de prueba
        const email = 'salomevasquez100@gmail.com';
        const password = 'root123';

        // Completar el formulario de inicio de sesión
        cy.get('input[name="email"]').type(email);
        cy.get('input[name="password"]').type(password);
        cy.get('button[type="submit"]').click();

        // Esperar la solicitud interceptada y verificar la redirección
        cy.wait('@loginRequest').its('response.statusCode').should('eq', 200);
        cy.url().should('include', '/bus'); // Verifica que redirija a la ruta correcta
        cy.contains('Bienvenido'); 
    });

    it('Debería mostrar un error con credenciales inválidas (simulado)', () => {
        // Interceptar la solicitud de inicio de sesión con error
        cy.intercept('POST', '/bus/login', {
            statusCode: 401,
            body: {
                success: false,
                message: 'El email no está registrado'
            }
        }).as('loginRequestError');

        // Intentar iniciar sesión con credenciales inválidas
        const email = 'usuario@noexiste.com'; // Email no registrado
        const password = 'contraseñaincorrecta'; // Contraseña incorrecta

        cy.get('input[name="email"]').type(email);
        cy.get('input[name="password"]').type(password);
        cy.get('button[type="submit"]').click();

        // Esperar la solicitud interceptada y verificar el mensaje de error
        cy.wait('@loginRequestError').its('response.statusCode').should('eq', 401);
        cy.contains('El email no está registrado').should('be.visible'); // Verificar el mensaje de error
    });
});
