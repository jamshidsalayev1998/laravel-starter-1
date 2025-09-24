import React from 'react';
import PrimaryButton from './PrimaryButton';
import LoadingSpinner from './LoadingSpinner';

const LoadingButton = ({
    loading = false,
    loadingText = 'Yuklanmoqda...',
    children,
    disabled = false,
    size = 'default',
    variant = 'primary',
    className = '',
    ...props
}) => {
    return (
        <PrimaryButton
            disabled={disabled || loading}
            size={size}
            variant={variant}
            className={`relative ${className}`}
            {...props}
        >
            {loading && (
                <div className="absolute inset-0 flex items-center justify-center">
                    <LoadingSpinner size="sm" color={variant === 'white' || variant === 'outline' ? 'gray' : 'white'} />
                </div>
            )}
            <span className={loading ? 'invisible' : 'visible'}>
                {children}
            </span>
        </PrimaryButton>
    );
};

export default LoadingButton;
