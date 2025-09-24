import React from 'react';

const LoadingSpinner = ({ 
    size = 'md', 
    color = 'indigo', 
    text = '', 
    className = '' 
}) => {
    const sizeClasses = {
        xs: 'h-4 w-4',
        sm: 'h-6 w-6', 
        md: 'h-8 w-8',
        lg: 'h-12 w-12',
        xl: 'h-16 w-16'
    };

    const colorClasses = {
        indigo: 'border-indigo-600',
        blue: 'border-blue-600',
        green: 'border-green-600',
        red: 'border-red-600',
        yellow: 'border-yellow-600',
        gray: 'border-gray-600',
        white: 'border-white'
    };

    return (
        <div className={`flex flex-col items-center justify-center ${className}`}>
            <div 
                className={`animate-spin rounded-full border-b-2 ${sizeClasses[size]} ${colorClasses[color]}`}
            ></div>
            {text && (
                <p className="mt-2 text-sm text-gray-600 animate-pulse">
                    {text}
                </p>
            )}
        </div>
    );
};

export default LoadingSpinner; 