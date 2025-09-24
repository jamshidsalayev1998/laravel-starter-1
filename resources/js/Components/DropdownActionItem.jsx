import React from 'react';
import LoadingSpinner from './LoadingSpinner';

const DropdownActionItem = ({
    onClick,
    loading = false,
    disabled = false,
    className = '',
    children,
    variant = 'default'
}) => {
    const variantClasses = {
        default: 'text-gray-700 hover:bg-gray-100',
        danger: 'text-red-600 hover:bg-red-50',
        success: 'text-green-600 hover:bg-green-50',
        warning: 'text-yellow-600 hover:bg-yellow-50'
    };

    return (
        <button
            onClick={onClick}
            disabled={disabled || loading}
            className={`
                w-full text-left px-4 py-2 text-sm transition-colors duration-150 ease-in-out
                flex items-center justify-between
                ${variantClasses[variant]}
                ${(disabled || loading) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
                ${className}
            `}
        >
            <span>{children}</span>
            {loading && (
                <LoadingSpinner size="sm" color="current" />
            )}
        </button>
    );
};

export default DropdownActionItem;
