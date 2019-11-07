#include "sap2ism.h"

bool QueryListAdd(TQueryList **queries, char *str)
{
	TQueryList *temp = NULL;

	check((queries != NULL) && (str != NULL), ERROR_STR_INVALIDINPUT);
	if (*queries == NULL)
	{
		temp = malloc(sizeof (TQueryList));
		check_mem(temp);
		temp->next = NULL;
		temp->query = strdup(str);
		check_mem(temp->query);
		*queries = temp;
		return true;
	}
	temp = *queries;
	while (temp->next != NULL)
		temp = temp->next;
	temp->next = malloc(sizeof (TQueryList));
	check_mem(temp->next);
	temp->next->next = NULL;
	temp->next->query = strdup(str);
	check_mem(temp->next->query);
	return true;
error:
	if (temp != NULL)
	{
		if (temp->next != NULL)
		{
			temp = temp->next;
		}
		if (temp->query != NULL)
			free(temp->query);
		free(temp);
	}	
	return false;
}

bool QueryListAppend(TQueryList *queries, TQueryList *appends)
{
	check((queries != NULL) && (appends != NULL), ERROR_STR_INVALIDINPUT);
	TQueryList *temp = queries;
	while (temp->next != NULL)
		temp = temp->next;
	temp->next = appends;
	return true;
error:
	return false;
}

void QueryListFree(TQueryList *queries)
{
	TQueryList *temp;
	while (queries != NULL)
	{
		temp = queries;
		queries = queries->next;
		if (temp->query != NULL)
			free(temp->query);
		free(temp);
	}
}