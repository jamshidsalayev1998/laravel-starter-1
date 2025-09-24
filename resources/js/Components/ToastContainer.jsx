import React, { useState, createContext, useContext } from 'react';
import Toast from './Toast';

const ToastContext = createContext();

export const useToast = () => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
};

export const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const addToast = (message, type = 'info', duration = 5000) => {
        const id = Date.now() + Math.random();
        setToasts(prev => [...prev, { id, message, type, duration }]);
    };

    const removeToast = (id) => {
        setToasts(prev => prev.filter(toast => toast.id !== id));
    };

    const showSuccess = (message, duration) => addToast(message, 'success', duration);
    const showError = (message, duration) => addToast(message, 'error', duration);
    const showWarning = (message, duration) => addToast(message, 'warning', duration);
    const showInfo = (message, duration) => addToast(message, 'info', duration);

    return (
        <ToastContext.Provider value={{ 
            addToast, 
            removeToast, 
            showSuccess, 
            showError, 
            showWarning, 
            showInfo 
        }}>
            {children}
            <div className="fixed top-4 right-4 z-50 space-y-2">
                {toasts.map(toast => (
                    <Toast
                        key={toast.id}
                        message={toast.message}
                        type={toast.type}
                        duration={toast.duration}
                        onClose={() => removeToast(toast.id)}
                    />
                ))}
            </div>
        </ToastContext.Provider>
    );
};

// Default export qo'shamiz
const ToastContainer = ToastProvider;
export default ToastContainer; 