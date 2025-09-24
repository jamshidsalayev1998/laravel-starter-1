import React, { useState, useEffect } from 'react';

const Toast = ({ 
    message, 
    type = 'info', 
    duration = 5000, 
    onClose 
}) => {
    const [isVisible, setIsVisible] = useState(true);

    useEffect(() => {
        if (duration > 0) {
            const timer = setTimeout(() => {
                setIsVisible(false);
                setTimeout(() => onClose?.(), 300);
            }, duration);
            return () => clearTimeout(timer);
        }
    }, [duration, onClose]);

    const typeClasses = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };

    const iconClasses = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    if (!isVisible) return null;

    return (
        <div className={`fixed top-4 right-4 z-50 max-w-sm w-full ${typeClasses[type]} rounded-lg shadow-lg transform transition-all duration-300 ease-in-out`}>
            <div className="p-4">
                <div className="flex items-start">
                    <div className="flex-shrink-0">
                        <span className="text-lg">{iconClasses[type]}</span>
                    </div>
                    <div className="ml-3 flex-1">
                        <p className="text-sm font-medium">{message}</p>
                    </div>
                    <div className="ml-4 flex-shrink-0">
                        <button
                            onClick={() => {
                                setIsVisible(false);
                                setTimeout(() => onClose?.(), 300);
                            }}
                            className="text-white hover:text-gray-200 focus:outline-none"
                        >
                            <span className="sr-only">Yopish</span>
                            <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Toast;
