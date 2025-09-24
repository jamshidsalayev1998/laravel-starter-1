# StayNow API Documentation

## 📋 **Umumiy ma'lumotlar**

- **Base URL:** `http://127.0.0.1:8000/api`
- **Authentication:** Laravel Sanctum (Bearer Token)
- **Content-Type:** `application/json`
- **Response Format:** JSON

## 🔐 **Authentication Endpoints**

### 1. **Ro'yxatdan o'tish**
```http
POST /api/auth/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "phone": "+998901234567",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "phone": "+998901234567",
            "email": "john@example.com",
            "is_active": true,
            "created_at": "2025-09-12T10:00:00.000000Z"
        },
        "access_token": "1|abc123...",
        "refresh_token": "def456...",
        "expires_in": 900
    }
}
```

### 2. **Login**
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "phone": "+998901234567",
    "password": "password"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "phone": "+998901234567",
            "email": "john@example.com",
            "is_active": true,
            "roles": [
                {
                    "id": 1,
                    "name": "guest",
                    "display_name": "Guest"
                }
            ],
            "permissions": []
        },
        "access_token": "1|abc123...",
        "refresh_token": "def456...",
        "expires_in": 900,
        "device": {
            "id": "device_123",
            "name": "Chrome on Windows",
            "type": "desktop"
        }
    }
}
```

### 3. **Telefon raqamini tasdiqlash**
```http
POST /api/auth/verify-phone
```

**Request Body:**
```json
{
    "phone": "+998901234567",
    "verification_code": "123456"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Phone verified successfully"
}
```

### 4. **Token yangilash**
```http
POST /api/auth/refresh-token
```

**Request Body:**
```json
{
    "refresh_token": "def456..."
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "access_token": "2|xyz789...",
        "expires_in": 900
    }
}
```

### 5. **Foydalanuvchi ma'lumotlari**
```http
GET /api/auth/me
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "phone": "+998901234567",
            "email": "john@example.com",
            "is_active": true,
            "is_banned": false,
            "roles": [...],
            "permissions": [...]
        }
    }
}
```

### 6. **Chiqish**
```http
POST /api/auth/logout
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### 7. **Qurilmalar ro'yxati**
```http
GET /api/auth/devices
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "devices": [
            {
                "id": "device_123",
                "device_name": "Chrome on Windows",
                "device_type": "desktop",
                "platform": "Windows",
                "browser": "Chrome",
                "location": "Tashkent, Uzbekistan",
                "last_activity": "2025-09-12T10:00:00.000000Z",
                "is_current": true
            }
        ]
    }
}
```

### 8. **Qurilmani o'chirish**
```http
DELETE /api/auth/devices/{session_id}
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Device revoked successfully"
}
```

### 9. **Barcha qurilmalarni o'chirish**
```http
DELETE /api/auth/devices
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "All devices revoked successfully"
}
```

## 👥 **Admin Endpoints**

### 1. **Foydalanuvchilar ro'yxati**
```http
GET /api/admin/users
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `page` (optional): Sahifa raqami
- `per_page` (optional): Sahifadagi elementlar soni
- `search` (optional): Qidiruv so'zi
- `status` (optional): `active`, `inactive`, `banned`

**Response (200):**
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "name": "John Doe",
                "phone": "+998901234567",
                "email": "john@example.com",
                "is_active": true,
                "is_banned": false,
                "created_at": "2025-09-12T10:00:00.000000Z",
                "roles": [...]
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 100,
            "last_page": 7
        }
    }
}
```

### 2. **Foydalanuvchi tafsilotlari**
```http
GET /api/admin/users/{user_id}
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "phone": "+998901234567",
            "email": "john@example.com",
            "is_active": true,
            "is_banned": false,
            "banned_at": null,
            "ban_reason": null,
            "ban_expires_at": null,
            "created_at": "2025-09-12T10:00:00.000000Z",
            "roles": [...],
            "permissions": [...],
            "sessions": [...]
        }
    }
}
```

### 3. **Foydalanuvchini ban qilish**
```http
POST /api/admin/users/{user_id}/ban
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "reason": "Spam content",
    "expires_at": "2025-10-12T10:00:00.000000Z" // optional, null bo'lsa doimiy ban
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "User banned successfully"
}
```

### 4. **Foydalanuvchini ban'dan chiqarish**
```http
POST /api/admin/users/{user_id}/unban
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "User unbanned successfully"
}
```

### 5. **Foydalanuvchini faollashtirish**
```http
POST /api/admin/users/{user_id}/activate
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "User activated successfully"
}
```

### 6. **Foydalanuvchini deaktivatsiya qilish**
```http
POST /api/admin/users/{user_id}/deactivate
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "message": "User deactivated successfully"
}
```

## 👑 **Super Admin Endpoints**

### **Rollar boshqaruvi**

#### 1. **Rollar ro'yxati**
```http
GET /api/super-admin/roles
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "roles": [
            {
                "id": 1,
                "name": "super-admin",
                "display_name": "Super Admin",
                "description": "Full system access",
                "permissions_count": 15,
                "users_count": 1,
                "created_at": "2025-09-12T10:00:00.000000Z"
            }
        ]
    }
}
```

#### 2. **Mavjud ruxsatlar**
```http
GET /api/super-admin/roles/available
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "permissions": [
            {
                "id": 1,
                "name": "users.view",
                "display_name": "View Users",
                "group": "users"
            }
        ]
    }
}
```

#### 3. **Role yaratish**
```http
POST /api/super-admin/roles
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "name": "moderator",
    "display_name": "Moderator",
    "description": "Content moderator",
    "permissions": [1, 2, 3]
}
```

#### 4. **Role yangilash**
```http
PUT /api/super-admin/roles/{role_id}
Authorization: Bearer {access_token}
```

#### 5. **Role o'chirish**
```http
DELETE /api/super-admin/roles/{role_id}
Authorization: Bearer {access_token}
```

#### 6. **Role'ga ruxsatlar berish**
```http
POST /api/super-admin/roles/{role_id}/permissions
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "permissions": [1, 2, 3, 4]
}
```

### **Ruxsatlar boshqaruvi**

#### 1. **Ruxsatlar ro'yxati**
```http
GET /api/super-admin/permissions
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "permissions": [
            {
                "id": 1,
                "name": "users.view",
                "display_name": "View Users",
                "group": "users",
                "description": "Can view user list",
                "created_at": "2025-09-12T10:00:00.000000Z"
            }
        ],
        "groups": [
            {
                "name": "users",
                "display_name": "User Management",
                "permissions_count": 6
            }
        ]
    }
}
```

#### 2. **Ruxsat yaratish**
```http
POST /api/super-admin/permissions
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "name": "reports.view",
    "display_name": "View Reports",
    "group": "reports",
    "description": "Can view system reports"
}
```

#### 3. **Ko'p ruxsat yaratish**
```http
POST /api/super-admin/permissions/bulk
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "permissions": [
        {
            "name": "reports.users",
            "display_name": "User Reports",
            "group": "reports"
        },
        {
            "name": "reports.activity",
            "display_name": "Activity Reports",
            "group": "reports"
        }
    ]
}
```

### **Foydalanuvchi rollari va ruxsatlari**

#### 1. **Mavjud rollar**
```http
GET /api/super-admin/user-roles/available
Authorization: Bearer {access_token}
```

#### 2. **Mavjud ruxsatlar**
```http
GET /api/super-admin/user-roles/permissions/available
Authorization: Bearer {access_token}
```

#### 3. **Foydalanuvchi rollari va ruxsatlari**
```http
GET /api/super-admin/user-roles/{user_id}/roles-permissions
Authorization: Bearer {access_token}
```

#### 4. **Foydalanuvchiga role berish**
```http
POST /api/super-admin/user-roles/{user_id}/assign-role
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "role_id": 2
}
```

#### 5. **Foydalanuvchidan role olib tashlash**
```http
DELETE /api/super-admin/user-roles/{user_id}/roles/{role_id}
Authorization: Bearer {access_token}
```

#### 6. **Foydalanuvchi rollarini yangilash**
```http
POST /api/super-admin/user-roles/{user_id}/sync-roles
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "role_ids": [1, 2, 3]
}
```

#### 7. **Foydalanuvchiga to'g'ridan-to'g'ri ruxsat berish**
```http
POST /api/super-admin/user-roles/{user_id}/assign-permission
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
    "permission_id": 5
}
```

## 📝 **Error Responses**

### **Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "phone": ["The phone field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### **Unauthorized (401):**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### **Forbidden (403):**
```json
{
    "success": false,
    "message": "Insufficient permissions"
}
```

### **User Banned (403):**
```json
{
    "success": false,
    "message": "Account is banned",
    "data": {
        "ban_reason": "Spam content",
        "banned_at": "2025-09-12T10:00:00.000000Z",
        "ban_expires_at": "2025-10-12T10:00:00.000000Z"
    }
}
```

### **Not Found (404):**
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### **Server Error (500):**
```json
{
    "success": false,
    "message": "Internal server error"
}
```

## 🔧 **Frontend Integration Examples**

### **Axios Configuration:**
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Request interceptor - token qo'shish
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('access_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Response interceptor - token yangilash
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 401) {
            // Token yangilash logikasi
            const refreshToken = localStorage.getItem('refresh_token');
            if (refreshToken) {
                try {
                    const response = await api.post('/auth/refresh-token', {
                        refresh_token: refreshToken
                    });
                    localStorage.setItem('access_token', response.data.data.access_token);
                    return api.request(error.config);
                } catch (refreshError) {
                    // Login sahifasiga yo'naltirish
                    window.location.href = '/login';
                }
            }
        }
        return Promise.reject(error);
    }
);
```

### **Login Function:**
```javascript
const login = async (phone, password) => {
    try {
        const response = await api.post('/auth/login', {
            phone,
            password
        });
        
        const { user, access_token, refresh_token } = response.data.data;
        
        // Token'larni saqlash
        localStorage.setItem('access_token', access_token);
        localStorage.setItem('refresh_token', refresh_token);
        localStorage.setItem('user', JSON.stringify(user));
        
        return response.data;
    } catch (error) {
        throw error.response.data;
    }
};
```

### **Permission Check Hook:**
```javascript
import { usePermissions } from '@/Components/PermissionGate';

const MyComponent = () => {
    const { hasPermission, hasRole } = usePermissions();
    
    return (
        <div>
            {hasPermission('users.view') && (
                <button>View Users</button>
            )}
            
            {hasRole('admin') && (
                <div>Admin Panel</div>
            )}
        </div>
    );
};
```

## 📊 **Test Data**

### **Test Users:**
- **Super Admin:** `+998901234567` / `password`
- **Admin:** `+998901234568` / `password`
- **Guest:** `+998901234569` / `password`

### **Test Permissions:**
- `users.view` - Foydalanuvchilarni ko'rish
- `users.edit` - Foydalanuvchilarni tahrirlash
- `users.ban` - Foydalanuvchilarni ban qilish
- `users.unban` - Foydalanuvchilarni ban'dan chiqarish
- `roles.view` - Rollarni ko'rish
- `roles.edit` - Rollarni tahrirlash
- `permissions.view` - Ruxsatlarni ko'rish
- `permissions.edit` - Ruxsatlarni tahrirlash

## 🚀 **Quick Start**

1. **Server ishga tushiring:**
   ```bash
   php artisan serve
   ```

2. **Frontend build qiling:**
   ```bash
   npm run build
   ```

3. **API'ni test qiling:**
   ```bash
   curl -X POST http://127.0.0.1:8000/api/auth/login \
        -H "Content-Type: application/json" \
        -d '{"phone":"+998901234567","password":"password"}'
   ```

---

**📞 Support:** Agar savollar bo'lsa, loyiha maintainer'iga murojaat qiling.
