window.usuariosManager = () => ({
    // Estados
    showModal: false,
    showViewModal: false,
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
        password: '',
        password_confirmation: ''
    },
    
    usuarios: [],

    init() {
        console.log('🚀 usuariosManager inicializado');
        this.loadUsuarios();
        console.log('📊 Total usuarios cargados:', this.usuarios.length);
    },

    loadUsuarios() {
        try {
            const usuariosData = document.getElementById('usuarios-data');
            if (usuariosData) {
                this.usuarios = JSON.parse(usuariosData.textContent);
                console.log('📊 Usuarios cargados desde script:', this.usuarios.length);
            }
        } catch (error) {
            console.error('Error cargando usuarios:', error);
            this.usuarios = [];
        }
    },

    get filteredUsuarios() {
        return this.usuarios.filter(user => {
            const matchesSearch = !this.searchTerm || 
                user.name.toLowerCase().includes(this.searchTerm.toLowerCase());
            
            let matchesRole = true;
            if (this.roleFilter) {
                if (this.roleFilter === 'sin-rol') {
                    matchesRole = !user.rol;
                } else {
                    matchesRole = user.rol && user.rol.nombre === this.roleFilter;
                }
            }
            
            return matchesSearch && matchesRole;
        });
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
                password: '',
                password_confirmation: ''
            };
            this.showModal = true;
        }
    },

    async handleFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario...');
        
        this.isSubmitting = true;
        
        try {
            const formData = new FormData();
            formData.append('name', this.formData.name);
            formData.append('email', this.formData.email);
            
            if (!this.isEditing || this.formData.password) {
                formData.append('password', this.formData.password);
                formData.append('password_confirmation', this.formData.password_confirmation);
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            if (this.isEditing) {
                formData.append('_method', 'PUT');
            }

            const url = this.isEditing 
                ? `/usuarios/${this.editingUserId}`
                : '/usuarios';

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Éxito',
                        text: this.isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente',
                        icon: 'success',
                        timer: 2000
                    });
                }
                this.closeModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorData = await response.json();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error',
                        text: errorData.message || 'Error al procesar la solicitud',
                        icon: 'error'
                    });
                } else {
                    alert('Error al procesar la solicitud');
                }
            }
            
        } catch (error) {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión',
                    icon: 'error'
                });
            } else {
                alert('Error de conexión');
            }
        } finally {
            this.isSubmitting = false;
        }
    },

    closeModal() {
        this.showModal = false;
        this.isSubmitting = false;
        this.resetFormData();
    },

    resetFormData() {
        this.formData = {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        };
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

        if (typeof Swal !== 'undefined') {
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
        } else {
            if (confirm(`¿Estás seguro de eliminar al usuario "${user.name}"?`)) {
                this.submitDeleteForm(userId);
            }
        }
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

    clearFilters() {
        this.searchTerm = '';
        this.roleFilter = '';
    }
});