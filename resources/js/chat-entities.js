/**
 * Chat Entities Converter
 * Converts JSON API resource responses into clean entity objects
 */

class ChatEntities {
    /**
     * Convert a message resource to a clean message entity
     * @param {Object} messageResource - The JSON API message resource
     * @returns {Object} Clean message entity
     */
    static convertMessage(messageResource) {
        if (!messageResource || !messageResource.id) {
            console.warn('Invalid message resource:', messageResource);
            return null;
        }

        const sender = messageResource.relationships?.sender
            ? this.convertUser(messageResource.relationships.sender)
            : null;

        return {
            id: messageResource.id,
            content: messageResource.attributes?.content || '',
            type: messageResource.attributes?.type || 'text',
            mine: messageResource.attributes?.mine || false,
            createdAt: messageResource.attributes?.createdAt || null,
            updatedAt: messageResource.attributes?.updatedAt || null,
            sender: messageResource.relationships?.sender ? 
                this.convertUser(messageResource.relationships.sender) : null
        };
    }

    /**
     * Convert a user/participant resource to a clean user entity
     * @param {Object} userResource - The JSON API user resource
     * @returns {Object} Clean user entity
     */
    static convertUser(userResource) {
        if (!userResource || !userResource.id) {
            console.warn('Invalid user resource:', userResource);
            return null;
        }

        return {
            id: userResource.id,
            name: userResource.attributes?.name || 'Unknown User',
            avatar: userResource.attributes?.avatar || null,
            type: userResource.type || 'user',
            isAdmin: userResource.type === 'admin',
            isClient: userResource.type === 'client',
            isCleaner: userResource.type === 'cleaner',
            createdAt: userResource.attributes?.createdAt || null,
            updatedAt: userResource.attributes?.updatedAt || null
        };
    }

    /**
     * Convert a chat room resource to a clean chat room entity
     * @param {Object} roomResource - The JSON API chat room resource
     * @returns {Object} Clean chat room entity
     */
    static convertChatRoom(roomResource) {
        if (!roomResource || !roomResource.id) {
            console.warn('Invalid chat room resource:', roomResource);
            return null;
        }

        const t =  {
            id: roomResource.id,
            type: roomResource.attributes?.type || 'standard',
            createdAt: roomResource.attributes?.createdAt || null,
            updatedAt: roomResource.attributes?.updatedAt || null,
            user: roomResource.relationships?.participants ? 
                this.convertUser(roomResource.relationships.participants[0]) : null,
            participants: roomResource.relationships?.participants ? 
                roomResource.relationships.participants.map(p => this.convertUser(p)) : [],
            latestMessage: roomResource.relationships?.latestMessage ? 
                this.convertMessage(roomResource.relationships.latestMessage) : null
        };

        return t;
    }

    /**
     * Convert an array of message resources to clean message entities
     * @param {Array} messageResources - Array of JSON API message resources
     * @returns {Array} Array of clean message entities
     */
    static convertMessages(messageResources) {
        if (!Array.isArray(messageResources)) {
            console.warn('Expected array of message resources:', messageResources);
            return [];
        }

        return messageResources
            .map(resource => this.convertMessage(resource))
            .filter(message => message !== null);
    }

    /**
     * Convert an array of user resources to clean user entities
     * @param {Array} userResources - Array of JSON API user resources
     * @returns {Array} Array of clean user entities
     */
    static convertUsers(userResources) {
        if (!Array.isArray(userResources)) {
            console.warn('Expected array of user resources:', userResources);
            return [];
        }

        return userResources
            .map(resource => this.convertUser(resource))
            .filter(user => user !== null);
    }

    /**
     * Convert API response data to entities based on type
     * @param {Object|Array} data - The API response data
     * @param {String} type - The expected type ('message', 'user', 'chatroom')
     * @returns {Object|Array} Converted entities
     */
    static convert(data, type = null) {
        if (!data) return null;

        // Handle array responses
        if (Array.isArray(data)) {
            // Auto-detect type from first item if not specified
            if (!type && data.length > 0 && data[0].type) {
                type = data[0].type;
            }

            switch (type) {
                case 'message':
                    return this.convertMessages(data);
                case 'user':
                case 'admin':
                case 'client':
                case 'cleaner':
                    return this.convertUsers(data);
                default:
                    console.warn('Unknown array type for conversion:', type);
                    return data;
            }
        }

        // Handle single resource responses
        const resourceType = type || data.type;
        switch (resourceType) {
            case 'message':
                return this.convertMessage(data);
            case 'user':
            case 'admin':
            case 'client':
            case 'cleaner':
                return this.convertUser(data);
            case 'chat-room':
                return this.convertChatRoom(data);
            default:
                console.warn('Unknown resource type for conversion:', resourceType);
                return data;
        }
    }
}

// Make it available globally
window.ChatEntities = ChatEntities;
