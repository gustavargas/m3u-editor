<footer
    class="fi-footer my-3 pt-4 pb-6 flex flex-wrap items-center justify-center text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 text-center p-2 gap-4 mx-auto w-full px-4 md:px-6 lg:px-8 max-w-screen-xl">
    <span class="flex items-center gap-2">&copy; {{ date('Y') }} -
        <span class="flex items-center gap-2">
            <img src="/logo.svg" alt="m3u editor logo" width="20" height="20">
            m3u editor
        </span>
    </span>
    <span class="flex items-center gap-2">
        <a class="text-black dark:text-white" href="https://github.com/{{ config('dev.repo') }}" target="_blank">
            <svg viewBox="0 0 24 24" aria-hidden="true" class="w-6 h-6" style="fill: currentcolor;">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M12 2C6.477 2 2 6.463 2 11.97c0 4.404 2.865 8.14 6.839 9.458.5.092.682-.216.682-.48 0-.236-.008-.864-.013-1.695-2.782.602-3.369-1.337-3.369-1.337-.454-1.151-1.11-1.458-1.11-1.458-.908-.618.069-.606.069-.606 1.003.07 1.531 1.027 1.531 1.027.892 1.524 2.341 1.084 2.91.828.092-.643.35-1.083.636-1.332-2.22-.251-4.555-1.107-4.555-4.927 0-1.088.39-1.979 1.029-2.675-.103-.252-.446-1.266.098-2.638 0 0 .84-.268 2.75 1.022A9.607 9.607 0 0 1 12 6.82c.85.004 1.705.114 2.504.336 1.909-1.29 2.747-1.022 2.747-1.022.546 1.372.202 2.386.1 2.638.64.696 1.028 1.587 1.028 2.675 0 3.83-2.339 4.673-4.566 4.92.359.307.678.915.678 1.846 0 1.332-.012 2.407-.012 2.734 0 .267.18.577.688.48 3.97-1.32 6.833-5.054 6.833-9.458C22 6.463 17.522 2 12 2Z">
                </path>
            </svg>
        </a>
        <span>v{{ config('dev.version') }}</span>
    </span>
    <span class="flex items-center gap-2">
        <span>Donate with</span>
        <a class="text-black dark:text-white" href="{{ config('dev.paypal') }}" target="_blank">
            <svg class="h-4 w-auto" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                viewBox="121.565 23.381 286.048 76.225" enable-background="new 121.565 23.381 286.048 76.225"
                xml:space="preserve">
                <g>
                    <g>
                        <g>
                            <g>
                                <g>
                                    <path fill="currentColor"
                                        d="M314.585,23.381h-22.25c-1.521,0-2.816,1.107-3.053,2.609l-8.998,57.053
      c-0.178,1.126,0.691,2.146,1.832,2.146h11.416c1.064,0,1.973-0.775,2.137-1.827l2.553-16.175
      c0.236-1.503,1.531-2.609,3.054-2.609h7.041c14.655,0,23.113-7.093,25.324-21.151c0.995-6.147,0.04-10.979-2.839-14.36
      C327.638,25.347,322.029,23.381,314.585,23.381z M317.153,44.218c-1.216,7.987-7.315,7.987-13.216,7.987h-3.355l2.354-14.912
      c0.143-0.9,0.92-1.564,1.832-1.564h1.539c4.016,0,7.811,0,9.766,2.288C317.242,39.385,317.596,41.414,317.153,44.218z" />
                                    <path fill="currentColor"
                                        d="M155.89,23.381h-22.25c-1.521,0-2.816,1.107-3.054,2.609l-8.998,57.053
      c-0.177,1.126,0.693,2.146,1.833,2.146h10.624c1.521,0,2.816-1.107,3.054-2.61l2.428-15.392
      c0.237-1.503,1.532-2.609,3.053-2.609h7.041c14.656,0,23.114-7.093,25.325-21.151c0.995-6.147,0.04-10.979-2.838-14.36
      C168.941,25.347,163.333,23.381,155.89,23.381z M158.457,44.218c-1.216,7.987-7.316,7.987-13.215,7.987h-3.357l2.354-14.912
      c0.143-0.9,0.919-1.564,1.832-1.564h1.539c4.016,0,7.81,0,9.765,2.288C158.545,39.385,158.899,41.414,158.457,44.218z" />
                                    <path fill="currentColor"
                                        d="M222.393,43.963H211.74c-0.912,0-1.689,0.664-1.832,1.566l-0.469,2.979l-0.745-1.078
      c-2.308-3.351-7.45-4.469-12.585-4.469c-11.77,0-21.826,8.92-23.783,21.432c-1.019,6.24,0.427,12.205,3.966,16.367
      c3.251,3.825,7.891,5.417,13.419,5.417c9.487,0,14.75-6.096,14.75-6.096l-0.476,2.962c-0.179,1.126,0.691,2.146,1.832,2.146
      h9.595c1.521,0,2.815-1.107,3.053-2.609l5.761-36.473C224.402,44.98,223.533,43.963,222.393,43.963z M207.544,64.701
      c-1.028,6.088-5.861,10.174-12.025,10.174c-3.09,0-5.564-0.994-7.154-2.875c-1.576-1.866-2.169-4.523-1.669-7.483
      c0.959-6.033,5.87-10.252,11.94-10.252c3.025,0,5.482,1.004,7.103,2.902C207.371,59.08,208.012,61.755,207.544,64.701z" />
                                    <path fill="currentColor"
                                        d="M381.089,43.963h-10.653c-0.913,0-1.69,0.664-1.832,1.566l-0.47,2.979l-0.744-1.078
      c-2.309-3.351-7.45-4.469-12.585-4.469c-11.771,0-21.826,8.92-23.782,21.432c-1.02,6.24,0.428,12.205,3.966,16.367
      c3.251,3.825,7.892,5.417,13.419,5.417c9.487,0,14.75-6.096,14.75-6.096l-0.476,2.962c-0.179,1.126,0.69,2.146,1.832,2.146
      h9.595c1.521,0,2.814-1.107,3.053-2.609l5.76-36.473C383.1,44.98,382.229,43.963,381.089,43.963z M366.242,64.701
      c-1.029,6.088-5.861,10.174-12.025,10.174c-3.092,0-5.564-0.994-7.155-2.875c-1.575-1.866-2.169-4.523-1.669-7.483
      c0.96-6.033,5.869-10.252,11.939-10.252c3.024,0,5.481,1.004,7.104,2.902C366.066,59.08,366.708,61.755,366.242,64.701z" />
                                    <path fill="currentColor" d="M279.139,43.963H268.43c-1.024,0-1.981,0.509-2.558,1.355L251.1,67.075l-6.261-20.906
      c-0.393-1.309-1.596-2.206-2.961-2.206h-10.527c-1.271,0-2.165,1.25-1.756,2.454l11.792,34.612l-11.091,15.648
      c-0.871,1.229,0.008,2.928,1.514,2.928h10.697c1.015,0,1.964-0.497,2.541-1.33l35.615-51.4
      C281.515,45.645,280.634,43.963,279.139,43.963z" />
                                    <path fill="currentColor" d="M393.647,24.948l-9.133,58.097c-0.177,1.126,0.692,2.145,1.832,2.145h9.185
      c1.521,0,2.816-1.107,3.055-2.609l9.004-57.053c0.178-1.126-0.691-2.145-1.832-2.145H395.48
      C394.566,23.381,393.789,24.046,393.647,24.948z" />
                                </g>
                            </g>
                        </g>
                    </g>
                </g>
            </svg>
        </a>
    </span>
</footer>
