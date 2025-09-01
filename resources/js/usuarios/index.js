// Solo JavaScript puro, sin directivas Blade
window.usuariosManager = () => ({
    // Estados
    showModal: false,
    showViewModal: false,
    showDebug: true,
    isEditing: false,
    editingUserId: null,
    viewingUser: null,
    searchTerm: '',
    roleFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        name: '',
        email: '',
        rol_id: '',
        password: '',
        password_confirmation: ''
    },
    
    // Los datos se pasarán desde la vista
    usuarios: [],

    init() {
        console.log('🚀 usuariosManager inicializado');
        console.log('📊 Total usuarios cargados:', this.usuarios.length);
    },

    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/usuarios/${this.editingUserId}` 
            : `${baseUrl}/usuarios`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },

    isFormValid() {
        const nameValid = this.formData.name.trim().length > 0;
        const emailValid = this.formData.email.trim().length > 0 && this.formData.email.includes('@');
        
        let passwordValid = true;
        if (!this.isEditing) {
            passwordValid = this.formData.password.length >= 8 && 
                           this.formData.password === this.formData.password_confirmation;
        }

        const isValid = nameValid && emailValid && passwordValid;
        console.log('✅ Validación form:', { nameValid, emailValid, passwordValid, isValid });
        return isValid;
    },

    validateForm() {
        this.isFormValid();
    },

    openCreateModal() {
        console.log('➕ Abriendo modal para crear usuario');
        this.isEditing = false;
        this.editingUserId = null;
        this.resetFormData();
        this.showModal = true;
    },

    editUser(userId) {
        console.log('✏️ Editando usuario con ID:', userId);
        const user = this.usuarios.find(u => u.id === userId);
        if (user) {
            this.isEditing = true;
            this.editingUserId = userId;
            this.formData = {
                name: user.name,
                email: user.email,
                rol_id: user.rol_id || '',
                password: '',
                password_confirmation: ''
            };
            this.showModal = true;
        } else {
            console.error('❌ Usuario no encontrado:', userId);
            this.showSweetAlert('error', 'Error', 'Usuario no encontrado');
        }
    },

    async handleFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario...');
        
        this.isSubmitting = true;
        
        if (!this.isFormValid()) {
            console.error('❌ Formulario inválido');
            this.showSweetAlert('error', 'Error de validación', 'Por favor complete todos los campos correctamente');
            this.isSubmitting = false;
            return false;
        }

        try {
            const formData = new FormData();
            formData.append('name', this.formData.name);
            formData.append('email', this.formData.email);
            formData.append('rol_id', this.formData.rol_id);
            
            if (!this.isEditing || this.formData.password) {
                formData.append('password', this.formData.password);
                formData.append('password_confirmation', this.formData.password_confirmation);
            }
            
            // Agregar CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            if (this.isEditing) {
                formData.append('_method', 'PUT');
            }

            const response = await fetch(this.getFormAction(), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                this.showSweetAlert('success', 'Éxito', this.isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');
                this.closeModal();
                // Recargar la página o actualizar la lista
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorData = await response.json();
                this.showSweetAlert('error', 'Error', errorData.message || 'Error al procesar la solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.isSubmitting = false;
        }
    },

    closeModal() {
        console.log('🔒 Cerrando modal');
        this.showModal = false;
        this.isSubmitting = false;
        this.resetFormData();
    },

    resetFormData() {
        this.formData = {
            name: '',
            email: '',
            rol_id: '',
            password: '',
            password_confirmation: ''
        };
    },

    showSweetAlert(type, title, text) {
        const config = {
            title: title,
            text: text,
            icon: type,
            customClass: {
                popup: 'dark:bg-gray-800 dark:text-gray-100',
                title: 'dark:text-gray-100',
                content: 'dark:text-gray-300'
            }
        };

        if (type === 'success') {
            config.confirmButtonColor = '#10b981';
            config.timer = 3000;
        } else if (type === 'error') {
            config.confirmButtonColor = '#dc2626';
        }

        Swal.fire(config);
    },

    viewUser(userId) {
        const user = this.usuarios.find(u => u.id === userId);
        if (user) {
            this.viewingUser = user;
            this.showViewModal = true;
        }
    },

    closeViewModal() {
        this.showViewModal = false;
        this.viewingUser = null;
    },

    deleteUser(userId) {
        const user = this.usuarios.find(u => u.id === userId);
        if (!user) return;

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar al usuario "${user.name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submitDeleteForm(userId);
            }
        });
    },

    submitDeleteForm(userId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/usuarios/${userId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    },

    showUser(userId) {
        const user = this.usuarios.find(u => u.id === userId);
        if (!user) return false;

        if (this.searchTerm && !user.name.toLowerCase().includes(this.searchTerm.toLowerCase())) {
            return false;
        }

        if (this.roleFilter) {
            if (this.roleFilter === 'sin-rol' && user.rol) return false;
            if (this.roleFilter !== 'sin-rol' && (!user.rol || user.rol.nombre !== this.roleFilter)) return false;
        }

        return true;
    },

    filterUsers() {
        // El filtrado se hace en showUser()
    },

    clearFilters() {
        this.searchTerm = '';
        this.roleFilter = '';
    }
})