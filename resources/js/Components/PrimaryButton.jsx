export default function PrimaryButton({
    className = '',
    disabled,
    children,
    size = 'default',
    variant = 'primary',
    ...props
}) {
    // Base styles
    const baseStyles = 'inline-flex items-center justify-center font-medium transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    // Size variants
    const sizeStyles = {
        xs: 'h-7 px-3 text-xs rounded-md',
        sm: 'h-8 px-3 text-sm rounded-md',
        default: 'h-10 px-4 text-sm rounded-md',
        lg: 'h-11 px-6 text-base rounded-md'
    };

    // Variant styles
    const variantStyles = {
        primary: 'bg-gray-800 text-white hover:bg-gray-700 focus:bg-gray-700 focus:ring-gray-500 active:bg-gray-900',
        white: 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 focus:ring-gray-300 shadow-sm',
        outline: 'bg-transparent border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:ring-gray-300',
        ghost: 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-300'
    };

    return (
        <button
            {...props}
            className={`${baseStyles} ${sizeStyles[size]} ${variantStyles[variant]} ${disabled ? 'opacity-50' : ''} ${className}`}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
